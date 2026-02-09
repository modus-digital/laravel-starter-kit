<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

final class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $activeTab = (string) $request->query('tab', 'all');

        $query = $user->notifications()->latest();

        if ($activeTab === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query
            ->paginate(15)
            ->through(fn (DatabaseNotification $notification): array => NotificationResource::toArrayForUser($notification));

        return Inertia::render('core/notifications/index', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
            'activeTab' => $activeTab,
        ]);
    }

    public function show(Request $request, DatabaseNotification $notification): Response
    {
        $this->assertOwnsNotification($request->user(), $notification);
        $notification->markAsRead();

        return Inertia::render('core/notifications/show', [
            'notification' => NotificationResource::toArrayForUser($notification),
        ]);
    }

    public function markRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($request->user(), $notification);

        $notification->markAsRead();

        return back();
    }

    public function markUnread(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($request->user(), $notification);

        $notification->update(['read_at' => null]);

        return back();
    }

    public function destroy(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $this->assertOwnsNotification($request->user(), $notification);

        $notification->delete();

        return back();
    }

    public function bulkMarkRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'ids' => ['array'],
            'ids.*' => ['string'],
        ]);

        $ids = $validated['ids'] ?? [];

        if ($ids === []) {
            return back();
        }

        $user->notifications()
            ->whereIn('id', $ids)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }

    public function bulkMarkUnread(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'ids' => ['array'],
            'ids.*' => ['string'],
        ]);

        $ids = $validated['ids'] ?? [];

        if ($ids === []) {
            return back();
        }

        $user->notifications()
            ->whereIn('id', $ids)
            ->update(['read_at' => null]);

        return back();
    }

    public function clearAll(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->notifications()->delete();

        return back();
    }

    private function assertOwnsNotification(?User $user, DatabaseNotification $notification): void
    {
        if (! $user instanceof User || $notification->notifiable_type !== $user::class || $notification->notifiable_id !== $user->getKey()) {
            abort(403);
        }
    }
}
