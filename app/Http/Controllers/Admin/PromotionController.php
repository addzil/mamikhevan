<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::query()
            ->with(['product', 'category', 'giftProduct'])
            ->orderByDesc('starts_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('admin.promotions.create', $this->formOptions());
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        Promotion::create($this->payload($request));

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo berhasil ditambahkan.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.edit', ['promotion' => $promotion] + $this->formOptions());
    }

    public function update(PromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($this->payload($request));

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo berhasil dihapus.');
    }

    /** @return array{products: Collection<int, Product>, categories: Collection<int, Category>} */
    private function formOptions(): array
    {
        return [
            'products' => Product::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
        ];
    }

    /** @return array<string, mixed> */
    private function payload(PromotionRequest $request): array
    {
        $validated = $request->validated();
        $type = $validated['type'];
        $targetType = $validated['target_type'];

        return [
            'name' => $validated['name'],
            'event_name' => $validated['event_name'] ?? null,
            'voucher_code' => $validated['voucher_code'] ?? null,
            'type' => $type,
            'target_type' => $targetType,
            'product_id' => $targetType === Promotion::TARGET_PRODUCT ? $validated['product_id'] : null,
            'category_id' => $targetType === Promotion::TARGET_CATEGORY ? $validated['category_id'] : null,
            'discount_kind' => $type === Promotion::TYPE_SHIPPING_SUBSIDY ? Promotion::KIND_FIXED
                : (in_array($type, [Promotion::TYPE_PRICE, Promotion::TYPE_SHIPPING_DISCOUNT], true) ? $validated['discount_kind'] : null),
            'discount_value' => in_array($type, [Promotion::TYPE_PRICE, Promotion::TYPE_SHIPPING_DISCOUNT, Promotion::TYPE_SHIPPING_SUBSIDY], true)
                ? $validated['discount_value'] : 0,
            'maximum_discount' => in_array($type, [Promotion::TYPE_PRICE, Promotion::TYPE_SHIPPING_DISCOUNT], true)
                ? ($validated['maximum_discount'] ?? null) : null,
            'minimum_subtotal' => $validated['minimum_subtotal'] ?? 0,
            'buy_quantity' => $type === Promotion::TYPE_BUY_GET ? $validated['buy_quantity'] : null,
            'gift_product_id' => in_array($type, [Promotion::TYPE_BUY_GET, Promotion::TYPE_GIVEAWAY], true) ? $validated['gift_product_id'] : null,
            'gift_size' => in_array($type, [Promotion::TYPE_BUY_GET, Promotion::TYPE_GIVEAWAY], true) ? ($validated['gift_size'] ?? null) : null,
            'gift_quantity' => in_array($type, [Promotion::TYPE_BUY_GET, Promotion::TYPE_GIVEAWAY], true) ? $validated['gift_quantity'] : null,
            'gift_unit_price' => in_array($type, [Promotion::TYPE_BUY_GET, Promotion::TYPE_GIVEAWAY], true) ? $validated['gift_unit_price'] : null,
            'tiers' => $type === Promotion::TYPE_QUANTITY_TIER ? $this->tiers($validated['tiers'] ?? []) : null,
            'starts_at' => Carbon::createFromFormat('Y-m-d\TH:i', $validated['starts_at'], Promotion::TIMEZONE)->utc(),
            'ends_at' => Carbon::createFromFormat('Y-m-d\TH:i', $validated['ends_at'], Promotion::TIMEZONE)->utc(),
            'is_active' => $validated['is_active'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     * @return array<int, array{min_quantity: int, discount_kind: string, discount_value: int, maximum_discount: ?int}>
     */
    private function tiers(array $tiers): array
    {
        return collect($tiers)
            ->map(fn (array $tier) => [
                'min_quantity' => (int) $tier['min_quantity'],
                'discount_kind' => $tier['discount_kind'],
                'discount_value' => (int) $tier['discount_value'],
                'maximum_discount' => filled($tier['maximum_discount'] ?? null) ? (int) $tier['maximum_discount'] : null,
            ])
            ->sortBy('min_quantity')
            ->values()
            ->all();
    }
}
