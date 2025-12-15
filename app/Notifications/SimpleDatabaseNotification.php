<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * @phpstan-type NotificationPayload array{title: string, body?: string|null, action_url?: string|null}
 */
final class SimpleDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly ?string $body = null,
        public readonly ?string $actionUrl = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return NotificationPayload
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'action_url' => $this->actionUrl,
        ];
    }
}
