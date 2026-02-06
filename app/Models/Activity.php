<?php

declare(strict_types=1);

namespace App\Models;

use BackedEnum;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity as BaseActivity;
use Throwable;

use function in_array;
use function is_array;
use function is_object;
use function is_scalar;

/**
 * Custom Activity model that automatically:
 * - Transforms descriptions into translation keys based on the event field
 * - Injects issuer details (name, email, ip_address, user_agent) into properties
 * - Dynamically extracts identifiers from nested property arrays for translations
 *
 * @property int $id
 * @property string $log_name
 * @property string $description
 * @property string|null $event
 * @property string|null $subject_type
 * @property string|null $subject_id
 * @property string|null $causer_type
 * @property string|null $causer_id
 * @property Collection<string, mixed> $properties
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User|null $causer
 * @property-read \Illuminate\Database\Eloquent\Model|null $subject
 */
final class Activity extends BaseActivity
{
    /** @use HasFactory<\Database\Factories\ActivityFactory> */
    use HasFactory;

    /**
     * Get the translated description with replacements from properties.
     */
    public function getTranslatedDescription(): string
    {
        try {
            $payload = $this->getTranslationPayload();

            return __($payload['key'], $payload['replacements']);
        } catch (Throwable) {
            // Fallback to the raw description if translation fails
            return $this->description ?? 'Unknown activity';
        }
    }

    /**
     * Return the translation key and replacements for frontend rendering.
     *
     * @return array{key: string, replacements: array<string, string>}
     */
    public function getTranslationPayload(): array
    {
        try {
            $replacements = $this->buildTranslationReplacements();

            // Ensure all replacements are strings
            $stringReplacements = [];
            foreach ($replacements as $key => $value) {
                $stringReplacements[$key] = is_array($value) || is_object($value)
                    ? $this->formatValueForDisplay($value)
                    : (string) $value;
            }

            return [
                'key' => $this->description,
                'replacements' => $stringReplacements,
            ];
        } catch (Throwable) {
            return [
                'key' => $this->description ?? 'activity.unknown',
                'replacements' => [],
            ];
        }
    }

    protected static function booted(): void
    {
        parent::booted();

        self::creating(function (Activity $activity): void {
            // Build issuer details from causer
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
     * Dynamically processes all properties to extract human-friendly identifiers.
     *
     * @return array<string, mixed>
     */
    protected function buildTranslationReplacements(): array
    {
        $properties = $this->properties->toArray();
        $replacements = [];

        // Special handling for old/new values (used in task and user activity updates)
        if (array_key_exists('old', $properties) || array_key_exists('new', $properties)) {
            if (array_key_exists('old', $properties)) {
                $oldValue = $properties['old'];
                $replacements['old'] = $oldValue !== null
                    ? $this->formatTaskValueForDisplay($oldValue, $properties['field'] ?? null)
                    : 'empty';
            }
            if (array_key_exists('new', $properties)) {
                $newValue = $properties['new'];
                $replacements['new'] = $newValue !== null
                    ? $this->formatTaskValueForDisplay($newValue, $properties['field'] ?? null)
                    : 'empty';
            }
            if (isset($properties['field'])) {
                $replacements['field'] = $this->formatFieldName($properties['field']);
            }
        }

        // Process each property dynamically
        foreach ($properties as $key => $value) {
            // Skip special properties that need custom handling
            if (in_array($key, ['issuer', 'credentials', 'changes', 'old', 'new', 'field'], true)) {
                continue;
            }

            // Handle nested arrays (like user, client, role, permission, etc.)
            if (is_array($value)) {
                $identifier = $this->extractIdentifierFromArray($value);
                if ($identifier !== null) {
                    $replacements[$key] = $identifier;
                }
            } elseif (is_scalar($value) || $value === null) {
                // For scalar values, add them directly
                $replacements[$key] = $value !== null ? $this->formatValueForDisplay($value) : 'empty';
            }
        }

        // Special handling for issuer
        if (isset($properties['issuer']['name'])) {
            $replacements['issuer'] = (string) $properties['issuer']['name'];
        }

        // Add issuer email if not already set
        if (isset($properties['issuer']['email']) && ! isset($replacements['email'])) {
            $replacements['email'] = (string) $properties['issuer']['email'];
        }

        // Special handling for target
        if (isset($properties['target'])) {
            $replacements['target'] = (string) $properties['target'];
        } elseif (isset($properties['user']) && ! isset($properties['client'])) {
            // If only user is present (not with client), use it as target
            $replacements['target'] = $replacements['user'] ?? '';
        } elseif (isset($properties['client']) && ! isset($properties['user'])) {
            // If only client is present (not with user), use it as target
            $replacements['target'] = $replacements['client'] ?? '';
        }

        // Add credentials email for failed login attempts
        if (isset($properties['credentials']['email']) && ! isset($replacements['email'])) {
            $replacements['email'] = $properties['credentials']['email'];
        }

        return $replacements;
    }

    /**
     * Extract a human-friendly identifier from an array.
     * Tries to find name, email, or id in order of preference.
     *
     * @param  array<string, mixed>  $data
     */
    protected function extractIdentifierFromArray(array $data): ?string
    {
        // Try name first (most human-readable)
        if (isset($data['name']) && $data['name'] !== '') {
            return (string) $data['name'];
        }

        // Try email second (useful for users)
        if (isset($data['email']) && $data['email'] !== '') {
            return (string) $data['email'];
        }

        // Try id as last resort
        if (isset($data['id']) && $data['id'] !== '') {
            return (string) $data['id'];
        }

        return null;
    }

    /**
     * Format a value for display in activity logs.
     */
    protected function formatValueForDisplay(mixed $value): string
    {
        if (is_array($value)) {
            // Check if it's an enum-like array with 'value' and 'label'
            if (isset($value['value'], $value['label'])) {
                return (string) $value['label'];
            }

            // Otherwise, convert to JSON
            return json_encode($value) ?: '';
        }

        if (is_object($value)) {
            // If it's an enum or object with __toString, use that
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }

            // Handle BackedEnum instances
            if ($value instanceof BackedEnum) {
                return (string) $value->value;
            }

            // For other objects, return class name
            return $value::class;
        }

        // For scalars, just cast to string
        return (string) $value;
    }

    /**
     * Format a task value for display, handling special cases like status, priority, etc.
     */
    protected function formatTaskValueForDisplay(mixed $value, ?string $field = null): string
    {
        if ($value === null) {
            return '—';
        }

        if (is_array($value)) {
            // Handle status with name and color
            if (isset($value['name']) && isset($value['color'])) {
                return (string) $value['name'];
            }

            // Handle assigned user with name
            if (isset($value['name']) && $field === 'assigned_to_id') {
                return (string) $value['name'];
            }

            // Handle enum-like arrays with label
            if (isset($value['label'])) {
                return (string) $value['label'];
            }

            // Handle arrays with just a name
            if (isset($value['name'])) {
                return (string) $value['name'];
            }

            // Fallback to JSON
            return json_encode($value) ?: '';
        }

        // For scalars, format based on field type
        if ($field === 'due_date' && is_string($value)) {
            try {
                $date = \Carbon\Carbon::parse($value);

                return $date->format('M j, Y');
            } catch (Exception) {
                return (string) $value;
            }
        }

        return (string) $value;
    }

    /**
     * Format a field name for display.
     */
    protected function formatFieldName(string $field): string
    {
        return match ($field) {
            'title' => 'title',
            'description' => 'description',
            'status_id' => 'status',
            'priority' => 'priority',
            'type' => 'type',
            'due_date' => 'due date',
            'assigned_to_id' => 'assignment',
            default => str_replace('_', ' ', $field),
        };
    }
}
