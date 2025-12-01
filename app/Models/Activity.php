<?php

declare(strict_types=1);

namespace App\Models;

use BackedEnum;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as BaseActivity;
use Throwable;

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
        try {
            $replacements = $this->buildTranslationReplacements();

            // Ensure all replacements are strings
            $stringReplacements = [];
            foreach ($replacements as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $stringReplacements[$key] = $this->formatValueForDisplay($value);
                } else {
                    $stringReplacements[$key] = (string) $value;
                }
            }

            return __($this->description, $stringReplacements);
        } catch (Throwable $e) {
            // Fallback to the raw description if translation fails
            return $this->description ?? 'Unknown activity';
        }
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
            $replacements['issuer'] = (string) $properties['issuer']['name'];
        }

        // Add issuer email (for failed login attempts)
        if (isset($properties['issuer']['email'])) {
            $replacements['email'] = (string) $properties['issuer']['email'];
        }

        // Add target (for impersonation, user / client management)
        if (isset($properties['target'])) {
            $replacements['target'] = (string) $properties['target'];
        } elseif (isset($properties['user'])) {
            // Prefer a human-friendly identifier for users
            $user = $properties['user'];
            $targetValue = $user['name']
                ?? $user['email']
                ?? (string) ($user['id'] ?? '');
            $replacements['target'] = (string) $targetValue;
        } elseif (isset($properties['client'])) {
            // Prefer a human-friendly identifier for clients
            $client = $properties['client'];
            $targetValue = $client['name']
                ?? (string) ($client['id'] ?? '');
            $replacements['target'] = (string) $targetValue;
        }

        // Add attribute, old, and new values for field updates
        if (isset($properties['attribute'])) {
            $replacements['attribute'] = (string) $properties['attribute'];
        }
        if (array_key_exists('old', $properties)) {
            $oldValue = $properties['old'];
            $replacements['old'] = $oldValue !== null ? $this->formatValueForDisplay($oldValue) : 'empty';
        }
        if (array_key_exists('new', $properties)) {
            $newValue = $properties['new'];
            $replacements['new'] = $newValue !== null ? $this->formatValueForDisplay($newValue) : 'empty';
        }

        // Handle :user on :client translations
        if (isset($properties['user']) && isset($properties['client'])) {
            $user = $properties['user'];
            $client = $properties['client'];

            $replacements['user'] = $user['name']
                ?? $user['email']
                ?? (string) ($user['id'] ?? '');

            $replacements['client'] = $client['name']
                ?? (string) ($client['id'] ?? '');
        }

        // Add credentials email (for failed login with unknown user)
        if (isset($properties['credentials']['email'])) {
            $replacements['email'] = $properties['credentials']['email'];
        }

        return $replacements;
    }

    /**
     * Format a value for display in activity logs.
     */
    protected function formatValueForDisplay(mixed $value): string
    {
        if (is_array($value)) {
            // For arrays, try to find a more readable representation
            // Check if it's an enum-like array with 'value' and 'label'
            if (isset($value['value']) && isset($value['label'])) {
                return (string) $value['label'];
            }

            // Otherwise, convert to JSON
            return json_encode($value);
        }

        if (is_object($value)) {
            // If it's an enum or object with __toString, use that
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            // Otherwise, try to get a readable representation
            if ($value instanceof BackedEnum) {
                return $value->value;
            }

            // For other objects, return class name
            return get_class($value);
        }

        // For scalars, just cast to string
        return (string) $value;
    }
}
