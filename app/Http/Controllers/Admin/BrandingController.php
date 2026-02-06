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
            'logo_light' => Setting::get('branding.logo_light'),
            'logo_dark' => Setting::get('branding.logo_dark'),
            'emblem_light' => Setting::get('branding.emblem_light'),
            'emblem_dark' => Setting::get('branding.emblem_dark'),
            'app_name' => Setting::get('branding.app_name', config('app.name')),
            'tagline' => Setting::get('branding.tagline'),
            'primary_color' => Setting::get('branding.primary_color', '#3b82f6'),
            'secondary_color' => Setting::get('branding.secondary_color', '#8b5cf6'),
            'font' => Setting::get('branding.font', 'Inter'),
        ];

        return Inertia::render('core/admin/branding/edit', [
            'branding' => $branding,
        ]);
    }

    public function update(UpdateBrandingRequest $request, FileStorageService $fileStorage): RedirectResponse
    {
        // Handle light logo upload
        if ($request->hasFile('logo_light')) {
            $oldLogoLight = Setting::get('branding.logo_light');
            if ($oldLogoLight) {
                $fileStorage->delete($oldLogoLight);
            }

            $logoLightUrl = $fileStorage->upload(
                file: $request->file('logo_light'),
                storagePath: 'branding',
                public: true
            );
            Setting::set('branding.logo_light', $logoLightUrl);
        }

        // Handle dark logo upload
        if ($request->hasFile('logo_dark')) {
            $oldLogoDark = Setting::get('branding.logo_dark');
            if ($oldLogoDark) {
                $fileStorage->delete($oldLogoDark);
            }

            $logoDarkUrl = $fileStorage->upload(
                file: $request->file('logo_dark'),
                storagePath: 'branding',
                public: true
            );
            Setting::set('branding.logo_dark', $logoDarkUrl);
        }

        // Handle light emblem upload
        if ($request->hasFile('emblem_light')) {
            $oldEmblemLight = Setting::get('branding.emblem_light');
            if ($oldEmblemLight) {
                $fileStorage->delete($oldEmblemLight);
            }

            $emblemLightUrl = $fileStorage->upload(
                file: $request->file('emblem_light'),
                storagePath: 'branding',
                public: true
            );
            Setting::set('branding.emblem_light', $emblemLightUrl);
        }

        // Handle dark emblem upload
        if ($request->hasFile('emblem_dark')) {
            $oldEmblemDark = Setting::get('branding.emblem_dark');
            if ($oldEmblemDark) {
                $fileStorage->delete($oldEmblemDark);
            }

            $emblemDarkUrl = $fileStorage->upload(
                file: $request->file('emblem_dark'),
                storagePath: 'branding',
                public: true
            );
            Setting::set('branding.emblem_dark', $emblemDarkUrl);
        }

        // Update other branding settings
        Setting::set('branding.app_name', $request->app_name);
        Setting::set('branding.tagline', $request->tagline);
        Setting::set('branding.primary_color', $request->primary_color);
        Setting::set('branding.secondary_color', $request->secondary_color);
        Setting::set('branding.font', $request->font);

        Activity::inLog('administration')
            ->event('branding.updated')
            ->causedBy(Auth::user())
            ->log('activity.branding.updated');

        // Clear branding cache to regenerate color scales with new values
        app(BrandingService::class)->clearCache();

        return redirect()->route('admin.branding.edit')
            ->with('success', __('admin.branding.updated_successfully'));
    }
}
