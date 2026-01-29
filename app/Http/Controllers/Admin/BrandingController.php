<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBrandingRequest;
use App\Services\BrandingService;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Outerweb\Settings\Facades\Setting;
use Spatie\Activitylog\Facades\Activity;

final class BrandingController extends Controller
{
    public function edit(): Response
    {
        $branding = [
            'logo' => Setting::get('branding.logo'),
            'favicon' => Setting::get('branding.favicon'),
            'app_name' => Setting::get('branding.app_name', config('app.name')),
            'tagline' => Setting::get('branding.tagline'),
            'primary_color' => Setting::get('branding.primary_color', '#3b82f6'),
            'secondary_color' => Setting::get('branding.secondary_color', '#8b5cf6'),
            'font' => Setting::get('branding.font', 'Inter'),
            'logo_aspect_ratio' => Setting::get('branding.logo_aspect_ratio', '1:1'),
        ];

        return Inertia::render('core/admin/branding/edit', [
            'branding' => $branding,
        ]);
    }

    public function update(UpdateBrandingRequest $request, FileStorageService $fileStorage): RedirectResponse
    {
        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('branding.logo');
            if ($oldLogo) {
                $fileStorage->delete($oldLogo);
            }

            $logoUrl = $fileStorage->upload(
                file: $request->file('logo'),
                storagePath: 'branding',
                public: true
            );
            Setting::set('branding.logo', $logoUrl);
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            $oldFavicon = Setting::get('branding.favicon');
            if ($oldFavicon) {
                $fileStorage->delete($oldFavicon);
            }

            $faviconUrl = $fileStorage->upload(
                file: $request->file('favicon'),
                storagePath: 'branding',
                public: true
            );
            Setting::set('branding.favicon', $faviconUrl);
        }

        // Update other branding settings
        Setting::set('branding.app_name', $request->app_name);
        Setting::set('branding.tagline', $request->tagline);
        Setting::set('branding.primary_color', $request->primary_color);
        Setting::set('branding.secondary_color', $request->secondary_color);
        Setting::set('branding.font', $request->font);

        if ($request->has('logo_aspect_ratio')) {
            Setting::set('branding.logo_aspect_ratio', $request->logo_aspect_ratio);
        }

        Activity::inLog('administration')
            ->event('branding.updated')
            ->causedBy(Auth::user())
            ->log('');

        // Clear branding cache to regenerate color scales with new values
        app(BrandingService::class)->clearCache();

        return redirect()->route('admin.branding.edit')
            ->with('success', __('admin.branding.updated_successfully'));
    }
}
