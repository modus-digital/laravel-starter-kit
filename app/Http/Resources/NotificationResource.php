<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Notifications\DatabaseNotification;

final class NotificationResource
{
    /**
     * @return array{id: string, title: string, body: string|null, action_url: string|null, read_at: \Illuminate\Support\Carbon|null, created_at: \Illuminate\Support\Carbon, translation_key?: string, translation_replacements?: array<string, mixed>, context?: array<string, mixed>|null}
     */
    public static function toArrayForUser(DatabaseNotification $notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,
            'title' => (string) ($data['title'] ?? ''),
            'body' => $data['body'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
            'translation_key' => $data['translation_key'] ?? null,
            'translation_replacements' => $data['translation_replacements'] ?? null,
            'context' => $data['context'] ?? null,
        ];
    }
}
