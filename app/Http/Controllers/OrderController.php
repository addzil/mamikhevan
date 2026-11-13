<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Review;
use App\Models\User;
use App\Notifications\OrderCompletedAdminNotification;
use App\Notifications\PaymentProofUploadedAdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const COUNTRY_CODES = [
        '62' => 'Indonesia',
        '60' => 'Malaysia',
        '65' => 'Singapura',
        '673' => 'Brunei',
        '66' => 'Thailand',
        '63' => 'Filipina',
        '61' => 'Australia',
        '1' => 'Amerika Serikat / Kanada',
        '44' => 'Inggris',
    ];

    public function index(Request $request): View
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function track(): View
    {
        $countryCodes = self::COUNTRY_CODES;

        return view('orders.track', compact('countryCodes'));
    }

    public function lookup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9-]+$/'],
            'country_code' => ['nullable', Rule::in(array_keys(self::COUNTRY_CODES))],
            'phone' => ['required', 'string', 'regex:/^[0-9]{7,15}$/'],
        ], [
            'code.regex' => 'Masukkan kode pesanan menggunakan huruf dan angka saja.',
            'country_code.in' => 'Pilih kode negara yang tersedia.',
            'phone.regex' => 'Nomor telepon harus berisi 7–15 angka tanpa kode negara.',
        ]);

        $compactCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $validated['code']) ?? '');
        $possibleCodes = [$compactCode];

        if (str_starts_with($compactCode, 'KK')) {
            foreach ([5, 4] as $suffixLength) {
                $middle = substr($compactCode, 2, -$suffixLength);

                if (strlen($middle) >= 3) {
                    $possibleCodes[] = 'KK-'.$middle.'-'.substr($compactCode, -$suffixLength);
                }
            }
        }

        $lookupPhone = ($validated['country_code'] ?? '62').ltrim($validated['phone'], '0');
        $order = Order::query()->whereIn('code', $possibleCodes)->get()
            ->first(fn (Order $candidate) => hash_equals($this->normalizePhone($candidate->customer_phone), $lookupPhone));

        if (! $order) {
            throw ValidationException::withMessages([
                'code' => 'Kode pesanan atau nomor telepon tidak cocok.',
            ]);
        }

        $request->session()->put('orders.access.'.$order->id, true);

        return redirect()->route('orders.show', $order);
    }

    private function normalizePhone(string $phone): string
    {
        $normalizedPhone = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($normalizedPhone, '0')) {
            return '62'.substr($normalizedPhone, 1);
        }

        return $normalizedPhone;
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureOrderAccess($request, $order);

        $order->load(['items.product', 'items.review']);
        $paymentSettings = PaymentSetting::current();

        $reviewableItems = $this->reviewableItems($order);
        $reviewedCount = $reviewableItems->filter(fn (OrderItem $item) => $item->review !== null)->count();
        $canReview = $order->status === Order::STATUS_COMPLETED && $reviewedCount < $reviewableItems->count();
        $allReviewed = $reviewableItems->isNotEmpty() && $reviewedCount === $reviewableItems->count();

        return view('orders.show', compact('order', 'paymentSettings', 'canReview', 'allReviewed'));
    }

    public function confirmReceipt(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOrderAccess($request, $order);

        $result = DB::transaction(function () use ($order): string {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($lockedOrder->status === Order::STATUS_COMPLETED) {
                return 'completed';
            }

            if ($lockedOrder->status !== Order::STATUS_SHIPPED) {
                return 'not_shipped';
            }

            $lockedOrder->update([
                'status' => Order::STATUS_COMPLETED,
                'delivered_at' => now(),
            ]);

            return 'completed_now';
        });

        if ($result === 'completed') {
            return back()->with('info', 'Pesanan ini sudah dikonfirmasi diterima.');
        }

        if ($result === 'not_shipped') {
            return back()->with('error', 'Konfirmasi penerimaan hanya tersedia saat pesanan berstatus Dikirim.');
        }

        $this->notifyAdminsOfOrderCompletion($order->fresh(['items']));

        return back()->with('success', 'Terima kasih! Pesanan ditandai sudah diterima. Kamu bisa memberi penilaian sekarang.');
    }

    public function submitPaymentProof(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOrderAccess($request, $order);

        $validated = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'payment_proof.required' => 'Pilih gambar bukti pembayaran terlebih dahulu.',
            'payment_proof.image' => 'Bukti pembayaran harus berupa gambar.',
            'payment_proof.mimes' => 'Format bukti pembayaran harus JPG, PNG, atau WebP.',
            'payment_proof.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
        ]);

        $storedPath = null;

        try {
            $previousPath = DB::transaction(function () use ($order, $validated, &$storedPath): ?string {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                if ($lockedOrder->status !== Order::STATUS_PENDING || $lockedOrder->payment_method !== Order::PAYMENT_TRANSFER) {
                    throw ValidationException::withMessages([
                        'payment_proof' => 'Bukti pembayaran hanya dapat dikirim untuk pesanan transfer yang masih menunggu konfirmasi.',
                    ]);
                }

                $previousPath = $lockedOrder->payment_proof_path;
                $storedPath = $validated['payment_proof']->store('payment-proofs/'.$lockedOrder->id, 'local');

                if ($storedPath === false) {
                    throw ValidationException::withMessages([
                        'payment_proof' => 'Bukti pembayaran gagal disimpan. Silakan coba kembali.',
                    ]);
                }

                $lockedOrder->update([
                    'payment_proof_path' => $storedPath,
                    'payment_proof_uploaded_at' => now(),
                ]);

                return $previousPath;
            });
        } catch (\Throwable $exception) {
            if ($storedPath !== null) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }

        if ($previousPath !== null) {
            Storage::disk('local')->delete($previousPath);
        }

        $this->notifyAdminsOfPaymentProof($order->fresh(['items']));

        return redirect()->route('orders.show', $order)
            ->with('success', 'Bukti pembayaran berhasil dikirim. Pembayaran menunggu pemeriksaan admin.');
    }

    public function createReview(Request $request, Order $order): View|RedirectResponse
    {
        $this->ensureOrderAccess($request, $order);

        if ($order->status !== Order::STATUS_COMPLETED) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Penilaian dapat diisi setelah pesanan selesai.');
        }

        $order->load(['items.product', 'items.review']);

        $items = $this->reviewableItems($order)
            ->filter(fn (OrderItem $item) => $item->review === null);
        $reviewedItems = $order->items->filter(fn (OrderItem $item) => $item->review !== null);

        if ($items->isEmpty()) {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Semua produk pada pesanan ini sudah dinilai.');
        }

        return view('orders.reviews.create', compact('order', 'items', 'reviewedItems'));
    }

    public function storeReview(Request $request, Order $order): RedirectResponse
    {
        $this->ensureOrderAccess($request, $order);

        if ($order->status !== Order::STATUS_COMPLETED) {
            return back()->with('error', 'Penilaian dapat dikirim setelah pesanan selesai.');
        }

        $order->load(['items.review']);

        $items = $this->reviewableItems($order)
            ->filter(fn (OrderItem $item) => $item->review === null);

        if ($items->isEmpty()) {
            return redirect()->route('orders.show', $order)
                ->with('info', 'Semua produk pada pesanan ini sudah dinilai.');
        }

        $rules = [];

        foreach ($items as $item) {
            $rules["reviews.{$item->id}.rating"] = ['required', 'integer', 'between:1,5'];
            $rules["reviews.{$item->id}.product_quality"] = ['required', 'integer', 'between:1,5'];
            $rules["reviews.{$item->id}.shipping_speed"] = ['required', 'integer', 'between:1,5'];
            $rules["reviews.{$item->id}.service"] = ['required', 'integer', 'between:1,5'];
            $rules["reviews.{$item->id}.comment"] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($order, $items, $validated) {
            foreach ($items as $item) {
                $data = $validated['reviews'][$item->id] ?? null;

                if ($data === null) {
                    continue;
                }

                Review::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'user_id' => $order->user_id,
                    'reviewer_name' => $order->customer_name,
                    'reviewer_city' => $order->shipping_city,
                    'rating' => $data['rating'],
                    'product_quality' => $data['product_quality'],
                    'shipping_speed' => $data['shipping_speed'],
                    'service' => $data['service'],
                    'comment' => $data['comment'] ?? null,
                    'source' => Review::SOURCE_CUSTOMER,
                    'is_visible' => true,
                    'reviewed_at' => now(),
                ]);
            }
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Terima kasih! Penilaianmu sudah tersimpan.');
    }

    private function ensureOrderAccess(Request $request, Order $order): void
    {
        $user = $request->user();
        $isOwner = $user !== null && $order->user_id !== null && $order->user_id === $user->id;

        if ($user?->hasRole(User::ROLE_ADMINISTRATOR, User::ROLE_MANAGEMENT) || $isOwner) {
            return;
        }

        if ($request->session()->get('orders.access.'.$order->id) === true) {
            return;
        }

        if ($request->hasValidSignature(false)) {
            $request->session()->put('orders.access.'.$order->id, true);

            return;
        }

        abort(404);
    }

    private function reviewableItems(Order $order): Collection
    {
        return $order->items->filter(
            fn (OrderItem $item) => ! $item->is_promo_gift && $item->product_id !== null
        );
    }

    private function notifyAdminsOfPaymentProof(Order $order): void
    {
        $adminEmails = User::query()
            ->whereIn('role', User::ADMIN_ROLES)
            ->pluck('notification_email')
            ->filter()
            ->unique();

        foreach ($adminEmails as $adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new PaymentProofUploadedAdminNotification($order));
        }
    }

    private function notifyAdminsOfOrderCompletion(Order $order): void
    {
        $adminEmails = User::query()
            ->whereIn('role', User::ADMIN_ROLES)
            ->pluck('notification_email')
            ->filter()
            ->unique();

        foreach ($adminEmails as $adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new OrderCompletedAdminNotification($order));
        }
    }
}
