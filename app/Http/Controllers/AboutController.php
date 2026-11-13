<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $shippedOrders = Order::query()
            ->whereIn('status', [Order::STATUS_SHIPPED, Order::STATUS_COMPLETED])
            ->count();
        $averageRating = Review::query()->visible()->avg('rating');
        $reviewCount = Review::query()->visible()->count();
        $categoryCount = Category::query()->active()->count();
        $categories = Category::query()->active()->orderBy('name')->get();

        return view('about', compact('shippedOrders', 'averageRating', 'reviewCount', 'categoryCount', 'categories'));
    }
}
