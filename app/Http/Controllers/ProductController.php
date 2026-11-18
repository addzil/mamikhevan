<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $categorySlug = trim((string) $request->query('category'));
        $gender = $request->query('gender');
        $sort = (string) $request->query('sort', 'latest');
        $maxPrice = (int) $request->query('max_price');
        $saleOnly = $request->query('sale') === '1';
        $featuredOnly = $request->query('featured') === '1';

        $gender = in_array($gender, ['uniseks', 'laki-laki', 'perempuan'], true) ? $gender : null;
        $sort = in_array($sort, ['latest', 'price_asc', 'price_desc', 'popular', 'discount_desc'], true) ? $sort : 'latest';

        $products = Product::query()
            ->active()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('material', 'like', '%'.$search.'%');
                });
            })
            ->when($categorySlug !== '', function ($query) use ($categorySlug) {
                $query->whereHas('category', fn ($query) => $query->where('slug', $categorySlug));
            })
            ->when($gender, fn ($query) => $query->where('gender', $gender))
            ->when($maxPrice > 0, fn ($query) => $query->where('price', '<=', $maxPrice))
            ->when($saleOnly, fn ($query) => $query->onSale())
            ->when($featuredOnly, fn ($query) => $query->featured())
            ->when($sort === 'popular', fn (Builder $query) => $query->withSum([
                'orderItems as sold_quantity' => fn (Builder $items): Builder => $items
                    ->where('is_promo_gift', false)
                    ->whereHas('order', fn (Builder $orders): Builder => $orders->where('status', '!=', Order::STATUS_CANCELLED)),
            ], 'quantity'))
            ->when($sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($sort === 'popular', fn ($query) => $query->orderByDesc('sold_quantity'))
            ->when($sort === 'discount_desc', fn ($query) => $query->orderByRaw('COALESCE(compare_price - price, 0) DESC'))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->orderByDesc('id')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        $activeCategory = $categorySlug !== ''
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        return view('products.index', compact(
            'products',
            'categories',
            'activeCategory',
            'search',
            'gender',
            'sort',
            'maxPrice',
            'saleOnly',
            'featuredOnly'
        ));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'images', 'sizeGuideType.entries']);

        $reviewStats = $product->visibleReviews()
            ->selectRaw('COUNT(*) as total, AVG(rating) as rating_avg, AVG(product_quality) as product_quality_avg, AVG(shipping_speed) as shipping_speed_avg, AVG(service) as service_avg')
            ->first();

        $productReviews = $product->visibleReviews()
            ->orderByDesc('reviewed_at')
            ->latest()
            ->take(10)
            ->get();

        $relatedProducts = Product::query()
            ->active()
            ->whereKeyNot($product->id)
            ->when(
                $product->category_id,
                fn ($query) => $query->where('category_id', $product->category_id)
            )
            ->with('category')
            ->latest()
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts', 'reviewStats', 'productReviews'));
    }
}
