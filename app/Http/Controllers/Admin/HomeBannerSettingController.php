<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomeBannerSettingRequest;
use App\Models\HomeBannerSetting;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeBannerSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.home-banner.edit', [
            'banner' => HomeBannerSetting::current(),
            'products' => Product::query()->active()->with('images')->orderBy('name')->get(),
        ]);
    }

    public function update(HomeBannerSettingRequest $request): RedirectResponse
    {
        $banner = HomeBannerSetting::current();
        $cards = [];

        foreach ($request->validated('cards', []) as $index => $card) {
            $cards[] = [
                'label' => $card['label'] ?? '',
                'product_id' => $card['product_id'],
            ];
        }

        $banner->update($request->safe()->except('cards') + ['cards' => $cards]);

        return back()->with('success', 'Banner beranda berhasil diperbarui.');
    }
}
