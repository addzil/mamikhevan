<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\User;
use App\Notifications\OrderCreatedAdminNotification;
use App\Notifications\OrderPaymentNotification;
use App\Services\CartService;
use App\Services\KomerceShippingCostService;
use App\Services\PromotionService;
use App\Services\ShippingQuoteService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly ShippingQuoteService $quotes,
        private readonly KomerceShippingCostService $komerce,
        private readonly PromotionService $promotions,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $items = $this->cart->items();
        $voucherCode = $request->session()->get(GiveawayController::SESSION_KEY);
        $giveaway = $this->promotions->giveawayFor($voucherCode);

        if (empty($items) && $giveaway === null) {
            return redirect()->route('products.index')
                ->with('error', 'Keranjang masih kosong. Yuk pilih pakaian anak dulu!');
        }

        $subtotal = $this->cart->subtotal();

        $komerceEnabled = $this->komerce->enabled();

        $codAvailable = collect($items)->every(fn (array $item): bool => $item['product']->cod_available);
        $paymentDestinations = PaymentSetting::availableDestinations();

        return view('checkout.index', compact('items', 'subtotal', 'komerceEnabled', 'giveaway', 'voucherCode', 'codAvailable', 'paymentDestinations'));
    }

    public function destinations(Request $request): JsonResponse
    {
        abort_unless($this->komerce->enabled(), 404);

        $validated = $request->validate([
            'search' => ['required', 'string', 'min:3', 'max:100'],
        ]);

        try {
            $destinations = $this->komerce->searchDestinations($validated['search']);
        } catch (ConnectionException|RuntimeException) {
            return response()->json(['message' => 'Pencarian wilayah sedang tidak tersedia. Coba lagi.'], 503);
        }

        $request->session()->put('komerce_destinations', collect($destinations)->keyBy('id')->all());

        return response()->json(['destinations' => $destinations]);
    }

    public function quote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_province' => ['required', 'string', 'max:60'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_postal_code' => ['required', 'digits:5'],
            'shipping_destination_id' => [$this->komerce->enabled() ? 'required' : 'nullable', 'integer', 'min:1'],
            'voucher_code' => ['nullable', 'string', 'max:40'],
        ]);

        $items = $this->cart->items();

        $voucherCode = $this->voucherCodeFrom($request, $validated['voucher_code'] ?? null);
        $voucher = filled($voucherCode) ? $this->promotions->activeVoucher($voucherCode) : null;
        $giveaway = $this->promotions->giveawayFor($voucherCode);
        abort_if(empty($items) && $giveaway === null, 422);

        $voucherError = filled($voucherCode) && $voucher === null
            ? 'Kode voucher tidak ditemukan atau sudah tidak berlaku.'
            : null;

        if ($voucher?->type === Promotion::TYPE_GIVEAWAY && $giveaway === null) {
            $voucherError = 'Stok hadiah giveaway habis atau promo sudah berakhir.';
        }

        $chargeableItems = $this->shippingItems($items, $giveaway);
        $shippingItems = $chargeableItems !== [] ? $chargeableItems : $items;
        $subtotal = $this->cart->subtotal();

        if ($this->komerce->enabled()) {
            $this->validateDestination($request, $validated, false);

            try {
                $estimate = $this->quotes->estimateFromKomerce(
                    $shippingItems,
                    (int) $validated['shipping_destination_id'],
                    $validated['shipping_province'],
                    $validated['shipping_city'],
                );
            } catch (ConnectionException|RuntimeException) {
                return response()->json(['message' => 'Tarif Komerce sedang tidak tersedia. Coba lagi.'], 503);
            }
        } else {
            $estimate = $this->quotes->estimate(
                $shippingItems,
                $validated['shipping_province'],
                $validated['shipping_city'],
            );
        }

        $this->applyProductFreeShipping($estimate, $chargeableItems, $giveaway);

        foreach ($estimate['options'] as $courier => $option) {
            if ($giveaway !== null) {
                $estimate['options'][$courier]['payable_total'] = $subtotal + $option['cost'];
                $estimate['options'][$courier]['promotion'] = [
                    'name' => $giveaway['promotion']->displayName(),
                    'product_discount' => 0,
                    'shipping_discount' => 0,
                    'gift_name' => $giveaway['gift_product']->name,
                    'gift_quantity' => $giveaway['gift_quantity'],
                    'voucher_code' => $giveaway['promotion']->voucher_code,
                    'tier_min_quantity' => null,
                ];

                continue;
            }

            $promotion = $this->promotions->bestFor($items, $option['cost'], $voucherCode);
            $estimate['options'][$courier]['payable_total'] = $subtotal + $option['cost']
                - ($promotion['product_discount'] ?? 0) - ($promotion['shipping_discount'] ?? 0);
            $estimate['options'][$courier]['promotion'] = $promotion === null ? null : [
                'name' => $promotion['promotion']->displayName(),
                'product_discount' => $promotion['product_discount'],
                'shipping_discount' => $promotion['shipping_discount'],
                'gift_name' => $promotion['gift_product']?->name,
                'gift_quantity' => $promotion['gift_quantity'],
                'voucher_code' => $promotion['voucher_code'],
                'tier_min_quantity' => $promotion['tier_min_quantity'],
            ];
        }

        $estimate['voucher_error'] = $voucherError;

        return response()->json($estimate);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $paymentDestination = null;

        if ($request->validated('payment_method') === Order::PAYMENT_TRANSFER) {
            $paymentDestination = PaymentSetting::availableDestinations()[$request->validated('payment_destination')] ?? null;

            if ($paymentDestination === null) {
                throw ValidationException::withMessages([
                    'payment_destination' => 'Rekening atau QRIS yang dipilih sudah tidak tersedia. Pilih lagi.',
                ]);
            }
        }

        $items = $this->cart->items();

        $voucherCode = $this->voucherCodeFrom($request, $request->validated('voucher_code'));
        $voucher = filled($voucherCode) ? $this->promotions->activeVoucher($voucherCode) : null;

        if (filled($voucherCode) && $voucher === null) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kode voucher tidak ditemukan atau sudah tidak berlaku.',
            ]);
        }

        $giveaway = $this->promotions->giveawayFor($voucherCode);

        if ($voucher?->type === Promotion::TYPE_GIVEAWAY && $giveaway === null) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Stok hadiah giveaway habis atau promo sudah berakhir.',
            ]);
        }

        if (empty($items) && $giveaway === null) {
            return redirect()->route('products.index')
                ->with('error', 'Keranjang masih kosong.');
        }

        $chargeableItems = $this->shippingItems($items, $giveaway);
        $shippingItems = $chargeableItems !== [] ? $chargeableItems : $items;
        $liveEstimate = null;
        if ($this->komerce->enabled()) {
            $this->validateDestination($request, $request->validated(), true);

            try {
                $liveEstimate = $this->quotes->estimateFromKomerce(
                    $shippingItems,
                    (int) $request->validated('shipping_destination_id'),
                    $request->validated('shipping_province'),
                    $request->validated('shipping_city'),
                );
            } catch (ConnectionException|RuntimeException) {
                throw ValidationException::withMessages([
                    'shipping_courier' => 'Tarif Komerce sedang tidak tersedia. Coba lagi.',
                ]);
            }
        }

        $order = DB::transaction(function () use ($items, $request, $liveEstimate, $voucherCode, $giveaway, $paymentDestination) {
            $subtotal = 0;
            $preparedItems = [];
            $quoteItems = [];
            $reservedQuantities = [];
            $lockedProducts = [];
            $cartProductIds = collect($items)
                ->pluck('product.id')
                ->map(fn (int $productId): int => $productId)
                ->unique()
                ->sort()
                ->values();
            $cartProducts = Product::query()
                ->whereKey($cartProductIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $product = $cartProducts->get($item['product']->id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Produk "'.$item['product']->name.'" sudah tidak tersedia.',
                    ]);
                }

                if ($liveEstimate !== null) {
                    if ($product->price !== $item['product']->price
                        || $product->weight_grams !== $item['product']->weight_grams
                        || $product->free_shipping !== $item['product']->free_shipping) {
                        throw ValidationException::withMessages([
                            'cart' => 'Harga atau detail ongkir produk berubah. Muat ulang checkout untuk melihat ongkir terbaru.',
                        ]);
                    }
                }

                $size = $item['size'] ?: null;

                if ($product->sizeList() !== [] && ($size === null || ! in_array($size, $product->sizeList(), true))) {
                    throw ValidationException::withMessages([
                        'cart' => 'Pilih ukuran yang masih tersedia untuk produk "'.$product->name.'".',
                    ]);
                }

                $variantKey = $size ?? '';
                $reservedQuantities[$product->id][$variantKey] = ($reservedQuantities[$product->id][$variantKey] ?? 0) + $item['quantity'];

                $availableStock = $product->stockForSize($size);

                if ($availableStock < $reservedQuantities[$product->id][$variantKey]) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok ukuran '.($size ?? 'umum').' produk "'.$product->name.'" tinggal '.$availableStock.' buah. Silakan perbarui jumlah.',
                    ]);
                }

                $lockedProducts[$product->id] = $product;

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'category_name' => $product->category?->name,
                    'size' => $item['size'],
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineTotal,
                    'cost_price' => $product->cost_price,
                    'is_promo_gift' => false,
                ];

                $quoteItems[] = ['product' => $product, 'quantity' => $item['quantity']];
            }

            $chargeableItems = $this->shippingItems($quoteItems, $giveaway);
            $quoteShippingItems = $chargeableItems !== [] ? $chargeableItems : $quoteItems;
            $estimate = $liveEstimate ?? $this->quotes->estimate(
                $quoteShippingItems,
                $request->validated('shipping_province'),
                $request->validated('shipping_city'),
            );
            $this->applyProductFreeShipping($estimate, $chargeableItems, $giveaway);
            $selectedCourier = $request->validated('shipping_courier');
            $selectedShipping = $estimate['options'][$selectedCourier] ?? null;

            if ($selectedShipping === null) {
                throw ValidationException::withMessages([
                    'shipping_courier' => 'Kurir tidak tersedia untuk alamat atau berat paket ini.',
                ]);
            }

            if ($selectedCourier === Order::COURIER_GOSEND && $request->validated('payment_method') === Order::PAYMENT_COD) {
                throw ValidationException::withMessages([
                    'payment_method' => 'GoSend SameDay hanya tersedia dengan pembayaran transfer.',
                ]);
            }

            if ($request->validated('payment_method') === Order::PAYMENT_COD
                && collect($lockedProducts)->contains(fn (Product $product): bool => ! $product->cod_available)) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Salah satu produk di keranjang tidak tersedia untuk pembayaran COD. Pilih transfer atau hapus produk tersebut.',
                ]);
            }

            $shipping = $selectedShipping['cost'];
            $promotion = $giveaway === null ? $this->promotions->bestFor($quoteItems, $shipping, $voucherCode) : null;
            $productDiscount = $promotion['product_discount'] ?? 0;
            $shippingDiscount = $promotion['shipping_discount'] ?? 0;
            $giftProduct = null;
            $giftQuantity = 0;
            $giftUnitPrice = 0;
            $giftSize = null;
            $appliedPromotion = $giveaway['promotion'] ?? $promotion['promotion'] ?? null;

            if ($giveaway !== null) {
                $giftProduct = $cartProducts->get($giveaway['gift_product']->id)
                    ?? Product::query()->lockForUpdate()->find($giveaway['gift_product']->id);
                $giftQuantity = $giveaway['gift_quantity'];
                $giftUnitPrice = $giveaway['gift_unit_price'];
                $giftSize = $giveaway['promotion']->gift_size;
            } elseif (($promotion['gift_product'] ?? null) !== null) {
                $giftProduct = $cartProducts->get($promotion['gift_product']->id)
                    ?? Product::query()->lockForUpdate()->find($promotion['gift_product']->id);
                $giftQuantity = $promotion['gift_quantity'];
                $giftUnitPrice = $promotion['gift_unit_price'];
                $giftSize = $promotion['promotion']->gift_size;
            }

            if ($giftProduct !== null) {
                if (! $giftProduct->is_active
                    || $giftProduct->stockForSize($giftSize)
                        < ($reservedQuantities[$giftProduct->id][$giftSize ?? ''] ?? 0) + $giftQuantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok hadiah promo berubah. Muat ulang checkout dan coba lagi.',
                    ]);
                }

                $preparedItems[] = [
                    'product_id' => $giftProduct->id,
                    'product_name' => $giftProduct->name,
                    'category_name' => $giftProduct->category?->name,
                    'size' => $giftSize,
                    'price' => 0,
                    'original_price' => $giftUnitPrice,
                    'quantity' => $giftQuantity,
                    'subtotal' => 0,
                    'cost_price' => $giftProduct->cost_price,
                    'is_promo_gift' => true,
                ];
            }

            foreach ($lockedProducts as $productId => $product) {
                foreach ($reservedQuantities[$productId] as $variantKey => $quantity) {
                    $product->changeStockForSize($variantKey === '' ? null : $variantKey, -$quantity);
                }
            }

            if ($giftProduct !== null) {
                $giftProduct->changeStockForSize($giftSize, -$giftQuantity);
            }

            $amountBeforeUniqueCode = $subtotal - $productDiscount + $shipping - $shippingDiscount;
            $paymentUniqueCode = null;

            if ($request->validated('payment_method') === Order::PAYMENT_TRANSFER) {
                PaymentSetting::query()->orderBy('id')->lockForUpdate()->first();
                $paymentUniqueCode = $this->transferCode($amountBeforeUniqueCode);
            }

            $order = Order::create([
                'user_id' => $request->user()?->id,
                'customer_name' => $request->validated('customer_name'),
                'customer_phone' => $request->validated('customer_phone'),
                'customer_email' => $request->validated('customer_email'),
                'customer_address' => $request->validated('customer_address'),
                'shipping_province' => $request->validated('shipping_province'),
                'shipping_city' => $request->validated('shipping_city'),
                'shipping_district' => $request->validated('shipping_district'),
                'shipping_subdistrict' => $request->validated('shipping_subdistrict'),
                'shipping_postal_code' => $request->validated('shipping_postal_code'),
                'shipping_courier' => $selectedShipping['courier'],
                'shipping_service' => $selectedShipping['service'],
                'shipping_quote_source' => $selectedShipping['source'],
                'note' => $request->validated('note'),
                'payment_method' => $request->validated('payment_method'),
                'payment_destination' => $paymentDestination,
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'promotion_id' => $appliedPromotion?->id,
                'promotion_name' => $appliedPromotion?->displayName(),
                'voucher_code' => $giveaway !== null ? $giveaway['promotion']->voucher_code : ($promotion['voucher_code'] ?? null),
                'promotion_discount' => $productDiscount,
                'shipping_discount' => $shippingDiscount,
                'payment_unique_code' => $paymentUniqueCode,
                'total' => $amountBeforeUniqueCode + ($paymentUniqueCode ?? 0),
                'status' => Order::STATUS_PENDING,
            ]);

            $order->items()->createMany($preparedItems);

            return $order;
        }, attempts: 3);

        $this->cart->clear();
        $request->session()->forget(GiveawayController::SESSION_KEY);

        Notification::route('mail', $order->customer_email)
            ->notify(new OrderPaymentNotification($order));

        $adminEmails = User::query()->whereIn('role', User::ADMIN_ROLES)->pluck('notification_email')
            ->filter()
            ->unique()
            ->values();

        foreach ($adminEmails as $adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new OrderCreatedAdminNotification($order));
        }

        $destination = $request->user()
            ? route('orders.show', $order)
            : URL::signedRoute('orders.show', ['order' => $order], absolute: false);

        return redirect($destination)
            ->with('success', 'Pesanan berhasil dibuat! Simpan kode pesanan untuk melacak status.');
    }

    private function voucherCodeFrom(Request $request, ?string $submitted): ?string
    {
        return filled($submitted)
            ? $submitted
            : $request->session()->get(GiveawayController::SESSION_KEY);
    }

    /**
     * @param  array<int|string, array{product: Product, quantity: int}>  $items
     * @return array<int|string, array{product: Product, quantity: int}>
     */
    private function shippingItems(array $items, ?array $giveaway): array
    {
        $items = array_filter($items, fn (array $item): bool => ! $item['product']->free_shipping);

        if ($giveaway !== null) {
            $items['giveaway'] = [
                'product' => $giveaway['gift_product'],
                'quantity' => $giveaway['gift_quantity'],
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, array{product: Product, quantity: int}>  $items
     */
    /**
     * @param  array<string, mixed>  $estimate
     * @param  array<string, array{product: Product, quantity: int}>  $chargeableItems
     */
    private function applyProductFreeShipping(array &$estimate, array $chargeableItems, ?array $giveaway): void
    {
        if ($chargeableItems !== [] || $giveaway !== null) {
            return;
        }

        foreach ($estimate['options'] as &$option) {
            $option['cost'] = 0;
        }
        unset($option);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateDestination(Request $request, array $validated, bool $includeDistrict): void
    {
        $destination = $request->session()->get('komerce_destinations.'.($validated['shipping_destination_id'] ?? ''));

        $fields = [
            'shipping_province' => 'province_name',
            'shipping_city' => 'city_name',
            'shipping_postal_code' => 'zip_code',
        ];

        if ($includeDistrict) {
            $fields['shipping_district'] = 'district_name';
            $fields['shipping_subdistrict'] = 'subdistrict_name';
        }

        if (! is_array($destination)) {
            throw ValidationException::withMessages([
                'shipping_destination_id' => 'Pilih lokasi tujuan dari hasil pencarian Komerce.',
            ]);
        }

        foreach ($fields as $input => $providerField) {
            if (mb_strtolower(trim((string) ($validated[$input] ?? ''))) !== mb_strtolower(trim((string) ($destination[$providerField] ?? '')))) {
                throw ValidationException::withMessages([
                    'shipping_destination_id' => 'Data alamat berubah. Pilih ulang lokasi tujuan dari hasil pencarian.',
                ]);
            }
        }
    }

    private function transferCode(int $baseAmount): int
    {
        $usedAmounts = Order::query()
            ->where('payment_method', Order::PAYMENT_TRANSFER)
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED])
            ->whereBetween('total', [$baseAmount + 1, $baseAmount + 199])
            ->pluck('total')
            ->all();

        $start = random_int(1, 199);

        for ($offset = 0; $offset < 199; $offset++) {
            $code = (($start + $offset - 1) % 199) + 1;

            if (! in_array($baseAmount + $code, $usedAmounts, true)) {
                return $code;
            }
        }

        throw ValidationException::withMessages([
            'payment_method' => 'Nominal transfer unik sedang penuh. Silakan hubungi admin.',
        ]);
    }
}
