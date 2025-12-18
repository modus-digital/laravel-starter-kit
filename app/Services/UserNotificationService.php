<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RBAC\Permission;
use App\Models\User;
use App\Notifications\SimpleDatabaseNotification;
use Filament\Notifications\Notification as FilamentNotification;

final class UserNotificationService
{
    public function notify(
        User $user,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
    ): void {
        if ($user->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL->value)) {
            $notification = FilamentNotification::make()
                ->title($title);

            if ($body !== null) {
                $notification->body($body);
            }

            $user->notify(
                $notification->toDatabase()
            );

            return;
        }

        $user->notify(
            new SimpleDatabaseNotification(
                title: $title,
                body: $body,
                actionUrl: $actionUrl,
            )
        );
    }
}
