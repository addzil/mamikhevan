<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\HomeBannerSettingController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentSettingController as AdminPaymentSettingController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SizeGuideController as AdminSizeGuideController;
use App\Http\Controllers\Admin\StockOpnameController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GiveawayController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/storage/{path}', function (string $path) {
    $relativePath = str_replace('\\', '/', ltrim($path, '/'));
    $pathSegments = explode('/', $relativePath);

    abort_if(in_array('..', $pathSegments, true) || in_array('.', $pathSegments, true), 404);

    $storageRoots = [
        storage_path('app/public'),
        public_path('storage'),
        dirname(base_path()).'/storage',
    ];

    foreach ($storageRoots as $storageRoot) {
        $canonicalRoot = realpath($storageRoot);

        if ($canonicalRoot === false) {
            continue;
        }

        $candidatePath = realpath($canonicalRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath));

        if (
            $candidatePath !== false
            && is_file($candidatePath)
            && str_starts_with($candidatePath, $canonicalRoot.DIRECTORY_SEPARATOR)
        ) {
            return response()->file($candidatePath);
        }
    }

    abort(404);
})->where('path', '.*')->name('storage.public');

Route::get('/katalog', [ProductController::class, 'index'])->name('products.index');
Route::get('/katalog/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/tentang', AboutController::class)->name('about');
Route::view('/panduan-belanja', 'shopping-guide')->name('shopping-guide');
Route::view('/faq', 'faq')->name('faq');
Route::get('/kontak', [ContactController::class, 'index'])->name('contact.index');
Route::post('/kontak', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
Route::post('/keranjang/tambah', [CartController::class, 'add'])
    ->middleware('throttle:30,1')
    ->name('cart.add');
Route::patch('/keranjang', [CartController::class, 'update'])->name('cart.update');
Route::delete('/keranjang', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/keranjang/semua', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/klaim-hadiah', [GiveawayController::class, 'index'])->name('giveaway.claim');
Route::post('/klaim-hadiah', [GiveawayController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('giveaway.store');
Route::delete('/klaim-hadiah', [GiveawayController::class, 'clear'])->name('giveaway.clear');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/ongkir', [CheckoutController::class, 'quote'])
    ->middleware('throttle:20,1')
    ->name('checkout.quote');
Route::get('/checkout/destinasi', [CheckoutController::class, 'destinations'])
    ->middleware('throttle:30,1')
    ->name('checkout.destinations');
Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('checkout.store');

Route::get('/lacak-pesanan', [OrderController::class, 'track'])->name('orders.track');
Route::post('/lacak-pesanan', [OrderController::class, 'lookup'])
    ->middleware('throttle:5,1')
    ->name('orders.lookup');
Route::get('/pesanan/{order:code}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/pesanan/{order:code}/bukti-pembayaran', [OrderController::class, 'submitPaymentProof'])
    ->middleware('throttle:5,1')
    ->name('orders.payment-proof.store');
Route::post('/pesanan/{order:code}/terima', [OrderController::class, 'confirmReceipt'])->name('orders.confirm-receipt');
Route::get('/pesanan/{order:code}/ulasan', [OrderController::class, 'createReview'])->name('orders.reviews.create');
Route::post('/pesanan/{order:code}/ulasan', [OrderController::class, 'storeReview'])
    ->middleware('throttle:10,1')
    ->name('orders.reviews.store');

Route::get('/dashboard', function () {
    return auth()->user()?->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('orders.index');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::middleware('admin-role:administrator,management')->group(function () {
            Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
            Route::get('finance/export', [FinanceController::class, 'export'])->name('finance.export');
            Route::resource('promotions', AdminPromotionController::class)->except('show');
            Route::get('payment-settings', [AdminPaymentSettingController::class, 'edit'])->name('payment-settings.edit');
            Route::put('payment-settings', [AdminPaymentSettingController::class, 'update'])->name('payment-settings.update');

            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
            Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
            Route::get('orders/{order}/payment-proof', [AdminOrderController::class, 'paymentProof'])->name('orders.payment-proof');
            Route::get('orders/{order}/resi', [AdminOrderController::class, 'shippingLabel'])->name('orders.shipping-label');
            Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
            Route::patch('orders/{order}/tracking', [AdminOrderController::class, 'updateTrackingNumber'])->name('orders.tracking');
        });

        Route::middleware('admin-role:administrator,management,operator')->group(function () {
            Route::get('stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');
            Route::post('stock-opname/{product}', [StockOpnameController::class, 'update'])->name('stock-opname.update');
        });

        Route::middleware('admin-role:administrator')->group(function () {
            Route::get('audit-trail', [ActivityLogController::class, 'index'])->name('audit-trail.index');
            Route::resource('users', AdminUserController::class)->except('show');
        });

        Route::middleware('admin-role:administrator,management,operator')->group(function () {
            Route::resource('categories', AdminCategoryController::class)->except('show');
            Route::resource('products', AdminProductController::class)->except('show');
            Route::resource('reviews', AdminReviewController::class)->except('show');
            Route::get('home-banner', [HomeBannerSettingController::class, 'edit'])->name('home-banner.edit');
            Route::put('home-banner', [HomeBannerSettingController::class, 'update'])->name('home-banner.update');
            Route::patch('reviews/{review}/visibility', [AdminReviewController::class, 'toggleVisibility'])->name('reviews.visibility');
            Route::resource('size-guides', AdminSizeGuideController::class)->except('show');

            Route::get('messages', [AdminMessageController::class, 'index'])->name('messages.index');
            Route::get('messages/{message}', [AdminMessageController::class, 'show'])->name('messages.show');
            Route::post('messages/{message}/reply', [AdminMessageController::class, 'reply'])->name('messages.reply');
            Route::delete('messages/{message}', [AdminMessageController::class, 'destroy'])->name('messages.destroy');
        });
    });

require __DIR__.'/auth.php';
