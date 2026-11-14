<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HomeBannerSetting;
use App\Models\Product;
use App\Models\Review;
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
            ->latest()
            ->take(8)
            ->get();

        $saleProducts = Product::query()
            ->active()
            ->onSale()
            ->with('category')
            ->latest()
            ->take(8)
            ->get();

        $newestProducts = Product::query()
            ->active()
            ->with('category')
            ->latest()
            ->take(4)
            ->get();

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
