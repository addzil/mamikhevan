<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.site-settings.edit', ['settings' => SiteSetting::current()]);
    }

    public function update(SiteSettingRequest $request): RedirectResponse
    {
        $settings = SiteSetting::current();
        $oldLogoPath = $settings->logo_path;
        $logoPath = $request->boolean('remove_logo') ? null : $oldLogoPath;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('branding', 'public');

            if ($logoPath === false) {
                throw ValidationException::withMessages(['logo' => 'Logo gagal diunggah. Coba lagi.']);
            }
        }

        $settings->update(['logo_path' => $logoPath]);

        if ($oldLogoPath && $oldLogoPath !== $logoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return back()->with('success', 'Logo toko berhasil diperbarui.');
    }
}
