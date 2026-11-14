<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomeBannerSetting;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->featured()
            ->with('category')
            ->withSum(['orderItems as sold_quantity' => fn (Builder $query): Builder => $query
                ->where('is_promo_gift', false)
                ->whereHas('order', fn (Builder $orders): Builder => $orders->where('status', '!=', Order::STATUS_CANCELLED))], 'quantity')
            ->get()
            ->sort(fn (Product $first, Product $second): int => ($second->isInStock() <=> $first->isInStock())
                ?: ((int) $second->sold_quantity <=> (int) $first->sold_quantity)
                ?: ($second->created_at <=> $first->created_at)
                ?: ($second->id <=> $first->id))
            ->take(8);

        $saleProducts = Product::query()
            ->active()
            ->onSale()
            ->with('category')
            ->get()
            ->sort(fn (Product $first, Product $second): int => ($second->isInStock() <=> $first->isInStock())
                ?: (($second->compare_price - $second->price) <=> ($first->compare_price - $first->price))
                ?: ($second->created_at <=> $first->created_at)
                ?: ($second->id <=> $first->id))
            ->take(8);

        $newestProducts = Product::query()
            ->active()
            ->with('category')
            ->get()
            ->sort(fn (Product $first, Product $second): int => ($second->isInStock() <=> $first->isInStock())
                ?: ($second->created_at <=> $first->created_at)
                ?: ($second->id <=> $first->id))
            ->take(4);

        $reviews = Review::query()
            ->visible()
            ->orderByDesc('reviewed_at')
            ->latest()
            ->take(6)
            ->get();

        $storedHero = HomeBannerSetting::query()->first();
        $hero = $storedHero?->is_active ? $storedHero : ($storedHero === null ? HomeBannerSetting::current() : null);
        $heroProducts = $hero === null
            ? collect()
            : Product::query()->active()
                ->whereIn('id', collect($hero->cards ?? [])->pluck('product_id')->filter())
                ->get()->keyBy('id');

        return view('home', compact('categories', 'featuredProducts', 'saleProducts', 'newestProducts', 'reviews', 'hero', 'heroProducts'));
    }
}
