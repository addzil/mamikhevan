<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\HomeBannerSetting;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\SizeGuideEntry;
use App\Models\SizeGuideType;
use App\Models\User;
use App\Observers\ModelActivityObserver;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ([
            Category::class,
            ContactMessage::class,
            HomeBannerSetting::class,
            InventoryAdjustment::class,
            Order::class,
            OrderItem::class,
            PaymentSetting::class,
            Product::class,
            ProductImage::class,
            Promotion::class,
            Review::class,
            SizeGuideEntry::class,
            SizeGuideType::class,
            User::class,
        ] as $model) {
            $model::observe(ModelActivityObserver::class);
        }

        Event::listen(Login::class, function (Login $event): void {
            $actor = $event->user instanceof User ? $event->user : null;
            app(ActivityLogger::class)->record(
                'auth.login.succeeded',
                'Login berhasil.',
                $actor,
                ['guard' => $event->guard],
                $actor,
            );
        });

        Event::listen(Failed::class, function (Failed $event): void {
            $identifier = (string) ($event->credentials['email'] ?? $event->credentials['username'] ?? '');
            $targetUser = $event->user instanceof User ? $event->user : null;

            app(ActivityLogger::class)->record(
                'auth.login.failed',
                'Percobaan login gagal untuk '.($identifier !== '' ? $identifier : 'identitas yang tidak diketahui').'.',
                $targetUser,
                ['identifier' => $identifier, 'guard' => $event->guard],
                subjectLabel: $identifier !== '' ? $identifier : null,
            );
        });

        Event::listen(Logout::class, function (Logout $event): void {
            $actor = $event->user instanceof User ? $event->user : null;

            app(ActivityLogger::class)->record(
                'auth.logout',
                'Logout.',
                $actor,
                ['guard' => $event->guard],
                $actor,
            );
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event): void {
            $actor = $event->user instanceof User ? $event->user : null;
            app(ActivityLogger::class)->record('auth.password_reset', 'Kata sandi akun direset.', $actor, [], $actor);
        });

        Event::listen(Verified::class, function (Verified $event): void {
            $actor = $event->user instanceof User ? $event->user : null;
            app(ActivityLogger::class)->record('auth.email_verified', 'Email akun diverifikasi.', $actor, [], $actor);
        });

        View::composer('layouts.storefront', function ($view): void {
            $announcement = Cache::remember('storefront.header_announcement', now()->addMinute(), function (): ?string {
                $promotion = Promotion::query()
                    ->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->orderByDesc('starts_at')
                    ->orderByDesc('id')
                    ->first();

                if ($promotion === null) {
                    return null;
                }

                return $promotion->displayName().($promotion->hasVoucher() ? ' · Gunakan kode '.$promotion->voucher_code : '');
            });

            $view->with('storeAnnouncement', $announcement ?? 'Toko online pusat baju anak terpercaya · Kualitas brand terbaik');
        });

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
