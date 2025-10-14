<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Settings\UserSettings;
use App\Services\Validators\UserSettingsValidators;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * The UserSetting model represents a user's setting in the application.
 * It provides methods to access user settings, update them, and retrieve them.
 * This model is linked to the 'user_settings' table which is used to store user settings.
 *
 * @property string $user_id Foreign key to the user model
 * @property UserSettings $key The key of the user setting
 * @property array<string, mixed> $value The value of the user setting
 * @property array<string, mixed>|null $parsed_value The parsed value of the user setting
 * @property CarbonImmutable $created_at The timestamp of the user setting creation
 * @property CarbonImmutable $updated_at The timestamp of the user setting update
 * @property-read User $user The user that owns the user setting
 */
final class UserSetting extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * Get the user that owns the user setting.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
        );
    }

    /**
     * Get the parsed value of the user setting.
     *
     * @return Attribute<array<string, mixed>|null, never>
     */
    public function parsedValue(): Attribute
    {
        $validator = UserSettingsValidators::byKey($this->key, $this->value);

        return match (true) {
            $validator->fails() => Attribute::make(get: null),
            default => Attribute::make(get: fn (): array => $validator->validated()),
        };
    }

    public function updateValueAttribute(?string $path = null, mixed $value = null): void
    {
        $current = $this->value;

        $path !== null
            ? data_set(target: $current, key: $path, value: $value)
            : $current = $value;

        $validator = UserSettingsValidators::byKey(key: $this->key, data: $current);

        if ($validator->fails()) {
            throw new ValidationException(validator: $validator);
        }

        self::where('user_id', $this->user_id)
            ->where('key', $this->key)
            ->update(['value' => json_encode($validator->validated())]);
    }

    public function retrieve(UserSettings $key, ?string $path = null): mixed
    {
        $object = $this->where('key', $key)->first();

        if ($object === null) {
            throw new ModelNotFoundException(message: 'User setting not found');
        }

        return $path !== null
            ? data_get(target: $object->value, key: $path)
            : $object->value;
    }

    protected function casts(): array
    {
        return [
            'key' => UserSettings::class,
            'value' => 'array',
        ];
    }
}
