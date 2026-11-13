<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $source = (string) $request->query('source', 'all');
        $visibility = (string) $request->query('visibility', 'all');

        $reviews = Review::query()
            ->with('product')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('reviewer_name', 'like', '%'.$search.'%')
                        ->orWhere('reviewer_city', 'like', '%'.$search.'%')
                        ->orWhere('comment', 'like', '%'.$search.'%');
                });
            })
            ->when(in_array($source, Review::SOURCES, true), fn ($query) => $query->where('source', $source))
            ->when($visibility === 'visible', fn ($query) => $query->where('is_visible', true))
            ->when($visibility === 'hidden', fn ($query) => $query->where('is_visible', false))
            ->latest('reviewed_at')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Review::query()->count(),
            'average' => Review::query()->avg('rating'),
            'visible' => Review::query()->where('is_visible', true)->count(),
            'customer' => Review::query()->where('source', Review::SOURCE_CUSTOMER)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'search', 'source', 'visibility', 'stats'));
    }

    public function create(): View
    {
        $products = Product::query()->orderBy('name')->get();

        return view('admin.reviews.create', compact('products'));
    }

    public function store(ReviewRequest $request): RedirectResponse
    {
        Review::create($this->payload($request));

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Rating berhasil ditambahkan.');
    }

    public function edit(Review $review): View
    {
        $products = Product::query()->orderBy('name')->get();

        return view('admin.reviews.edit', compact('review', 'products'));
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $review->update($this->payload($request, $review));

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Rating berhasil diperbarui.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Rating berhasil dihapus.');
    }

    public function toggleVisibility(Review $review): RedirectResponse
    {
        $review->update(['is_visible' => ! $review->is_visible]);

        return back()->with('success', $review->is_visible
            ? 'Rating ditampilkan di toko.'
            : 'Rating disembunyikan dari toko.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ReviewRequest $request, ?Review $review = null): array
    {
        $data = $request->validated();
        $data['is_visible'] = $request->boolean('is_visible');
        $data['reviewed_at'] = $data['reviewed_at'] ?? now();

        $adminReply = trim((string) ($data['admin_reply'] ?? ''));
        $data['admin_reply'] = $adminReply !== '' ? $adminReply : null;

        if ($adminReply !== '' && ($review === null || $review->admin_reply !== $adminReply)) {
            $data['replied_at'] = now();
        } elseif ($adminReply === '') {
            $data['replied_at'] = null;
        }

        return $data;
    }
}
