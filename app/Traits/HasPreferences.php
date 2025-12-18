<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\Language;
use App\Enums\NotificationDeliveryMethod;
use App\Models\User;

trait HasPreferences
{
    public static function defaultPreferences(): array
    {
        $comments = config('modules.comments.enabled')
            ? ['comments' => NotificationDeliveryMethod::EMAIL_PUSH]
            : [];

        return [
            'notifications' => [
                'security_alerts' => NotificationDeliveryMethod::EMAIL,
                ...$comments,
            ],
            'language' => Language::EN,
        ];
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        return data_get($this->preferences, $key, $default);
    }

    public function setPreference(string $key, mixed $value): static
    {
        $preferences = $this->preferences ?? [];
        data_set($preferences, $key, $value);

        $this->preferences = $preferences;

        return $this;
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->preferences = array_replace_recursive(
                static::defaultPreferences(),
                $user->preferences ?? []
            );
        });
    }
}
