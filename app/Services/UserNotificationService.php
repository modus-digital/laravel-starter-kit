<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\SimpleDatabaseNotification;

final class UserNotificationService
{
    public function notify(
        User $user,
        string $title,
        ?string $body = null,
        ?string $actionUrl = null,
    ): void {
        $user->notify(
            new SimpleDatabaseNotification(
                title: $title,
                body: $body,
                actionUrl: $actionUrl,
            )
        );
    }
}
