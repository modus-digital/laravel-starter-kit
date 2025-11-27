<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as BaseActivity;

/**
 * Custom Activity model that automatically:
 * - Transforms descriptions into translation keys based on the event field
 * - Injects issuer details (name, email, ip_address, user_agent) into properties
 *
 * @property string|null $event
 * @property string $description
 * @property Collection<string, mixed> $properties
 * @property User|null $causer
 */
final class Activity extends BaseActivity
{
    /**
     * Get the translated description with replacements from properties.
     */
    public function getTranslatedDescription(): string
    {
        $replacements = $this->buildTranslationReplacements();

        return __($this->description, $replacements);
    }

    protected static function booted(): void
    {
        parent::booted();

        self::creating(function (Activity $activity): void {
            // Set description as translation key based on event
            if ($activity->event !== null && $activity->event !== '') {
                $activity->description = "activity.{$activity->event}";
            }

            // Build issuer details from causer or current auth user
            /** @var User|null $causer */
            $causer = $activity->causer;

            $issuer = [
                'name' => $causer->name ?? 'System',
                'email' => $causer->email ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            // Merge issuer details into properties (preserving existing properties)
            $existingProperties = $activity->properties->toArray();

            // Extract existing issuer data if present
            $existingIssuer = $existingProperties['issuer'] ?? [];
            unset($existingProperties['issuer']);

            // Merge with existing issuer data, filling in missing values
            $mergedIssuer = array_merge($issuer, $existingIssuer);

            // Append issuer as the last property
            $existingProperties['issuer'] = $mergedIssuer;

            $activity->properties = collect($existingProperties);
        });
    }

    /**
     * Build the array of replacements for translation.
     *
     * @return array<string, string>
     */
    protected function buildTranslationReplacements(): array
    {
        $properties = $this->properties->toArray();

        $replacements = [];

        // Add issuer name
        if (isset($properties['issuer']['name'])) {
            $replacements['issuer'] = $properties['issuer']['name'];
        }

        // Add issuer email (for failed login attempts)
        if (isset($properties['issuer']['email'])) {
            $replacements['email'] = $properties['issuer']['email'];
        }

        // Add target (for impersonation, user / client management)
        if (isset($properties['target'])) {
            $replacements['target'] = $properties['target'];
        } elseif (isset($properties['user'])) {
            // Prefer a human-friendly identifier for users
            $user = $properties['user'];
            $replacements['target'] = $user['name']
                ?? $user['email']
                ?? (string) ($user['id'] ?? '');
        } elseif (isset($properties['client'])) {
            // Prefer a human-friendly identifier for clients
            $client = $properties['client'];
            $replacements['target'] = $client['name']
                ?? (string) ($client['id'] ?? '');
        }

        // Add credentials email (for failed login with unknown user)
        if (isset($properties['credentials']['email'])) {
            $replacements['email'] = $properties['credentials']['email'];
        }

        return $replacements;
    }
}
