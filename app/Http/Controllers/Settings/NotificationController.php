<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateNotificationSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('settings/notifications');
    }

    public function update(UpdateNotificationSettingsRequest $request): RedirectResponse
    {
        $request
            ->user()
            ->setPreference(
                key: 'notifications',
                value: $request->input('notifications')
            );

        return back()->with('success', 'Notifications updated successfully');
    }
}
