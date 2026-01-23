<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Services\FileStorageService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => false, // User model doesn't implement MustVerifyEmail by default
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request, FileStorageService $fileStorage): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            // Refresh to ensure we have the latest avatar value
            $user->refresh();
            $oldAvatar = $user->attributes['avatar'] ?? null;

            if ($oldAvatar) {
                $fileStorage->delete($oldAvatar);
            }

            $data['avatar'] = $fileStorage->upload(
                file: $request->file('avatar'),
                storagePath: 'avatars',
                public: true
            );
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
