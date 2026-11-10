<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
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

        $newestProducts = Product::query()
            ->active()
            ->with('category')
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact('categories', 'featuredProducts', 'newestProducts'));
    }
}
