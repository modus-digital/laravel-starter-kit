<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateNotificationSettingsRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $defaultPreferences = $user::defaultPreferences()['notifications'] ?? [];

        $notificationsPreferences = (array) $user->getPreference(
            key: 'notifications',
            default: [],
        );

        $preferences = array_replace(
            $defaultPreferences,
            $notificationsPreferences,
        );

        return Inertia::render('settings/notifications', [
            'preferences' => $preferences,
        ]);
    }

    public function update(UpdateNotificationSettingsRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user
            ->setPreference(
                key: 'notifications',
                value: $request->input('notifications'),
            )
            ->save();

        return back()->with('data', [
            'toast' => [
                'title' => 'Saved',
                'description' => 'Your notification preferences have been updated.',
                'type' => 'success',
            ],
        ]);
    }
}
