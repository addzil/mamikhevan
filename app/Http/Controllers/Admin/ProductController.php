<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\SizeGuideType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $categoryId = (int) $request->query('category');
        $status = (string) $request->query('status');

        $products = Product::query()
            ->with('category')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'low', fn ($query) => $query->where('stock', '<=', 5))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'search', 'categoryId', 'status'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $sizeGuideTypes = SizeGuideType::with('entries')->orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'sizeGuideTypes'));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $this->productData($request);
        $uploaded = $this->storeUploadedImages($request);

        $data['image'] = $uploaded->shift();

        $product = Product::create($data);

        $product->images()->createMany(
            $uploaded->values()
                ->map(fn (string $path, int $index) => ['path' => $path, 'sort_order' => $index])
                ->all()
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $sizeGuideTypes = SizeGuideType::with('entries')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'sizeGuideTypes'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->productData($request);
        $product->load('images');

        $removedIds = (array) $request->input('removed_images', []);

        if ($removedIds !== []) {
            $product->images()
                ->whereIn('id', $removedIds)
                ->get()
                ->each(function (ProductImage $image) {
                    Storage::disk('public')->delete($image->path);
                    $image->delete();
                });
        }

        if ($request->boolean('remove_image')) {
            $this->deleteStoredImage($product);
            $data['image'] = null;
        }

        $uploaded = $this->storeUploadedImages($request);

        if ($uploaded->isNotEmpty()) {
            if (blank($data['image'] ?? $product->image)) {
                $data['image'] = $uploaded->shift();
            }

            $sortOrder = (int) $product->images()->max('sort_order') + 1;

            $product->images()->createMany(
                $uploaded->values()
                    ->map(fn (string $path, int $index) => [
                        'path' => $path,
                        'sort_order' => $sortOrder + $index,
                    ])
                    ->all()
            );
        }

        if (blank($data['image'] ?? $product->image)) {
            $promoted = $product->images()->first();

            if ($promoted) {
                $data['image'] = $promoted->path;
                $promoted->delete();
            }
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->images()->get()->each(function (ProductImage $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        });

        $this->deleteStoredImage($product);
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    private function productData(ProductRequest $request): array
    {
        $data = $request->validated();

        unset($data['images'], $data['removed_images'], $data['remove_image']);

        return $data;
    }

    private function storeUploadedImages(ProductRequest $request): Collection
    {
        return collect($request->file('images') ?? [])
            ->map(fn ($file) => $file->store('products', 'public'));
    }

    private function deleteStoredImage(Product $product): void
    {
        if ($product->image && ! str_starts_with($product->image, 'images/')) {
            Storage::disk('public')->delete($product->image);
        }
    }
}
