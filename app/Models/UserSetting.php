<?php

namespace App\Models;

use App\Enums\Settings\UserSettings;
use App\Services\Validators\UserSettingsValidators;
use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The UserSetting model represents a user's setting in the application.
 * It provides methods to access user settings, update them, and retrieve them.
 * This model is linked to the 'user_settings' table which is used to store user settings.
 *
 * @property string $id Unique identifier for the user setting
 * @property string $user_id Foreign key to the users table
 * @property UserSettings $key The key of the user setting
 * @property array $value The value of the user setting
 * @property Carbon $created_at Timestamp of the creation
 * @property Carbon $updated_at Timestamp of the last update
 * @property-read User $user The user that owns the user setting
 */
class UserSetting extends Model
{
    protected $table = 'user_settings';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    /**
     * The parsed value attribute.
     *
     * @return Attribute<array<string, mixed>|null>
     */
    public function parsedValue(): Attribute
    {
        $validator = UserSettingsValidators::byKey($this->key, $this->value);

        if ($validator->fails()) {
            return Attribute::make(
                get: null,
            );
        }

        return Attribute::make(
            get: fn() => $validator->validated(),
        );
    }

    public function updateValueAttribute(?string $path = null, mixed $newValue = null): void
    {
        $currentValue = $this->value;

        if ($path !== null) {
            data_set($currentValue, $path, $newValue);
        } else {
            $currentValue = $newValue;
        }

        $validator = UserSettingsValidators::byKey(key: $this->key, data: $currentValue);

        if ($validator->fails()) {
            throw new Exception('Invalid value provided for user setting: ' . $validator->errors()->first());
        }

        UserSetting::where('user_id', $this->user_id)
            ->where('key', $this->key)
            ->update([
                'value' => json_encode($validator->validated()),
            ]);
    }

    public function retrieve(UserSettings $key, ?string $path = null): mixed
    {
        $object = $this->where('key', $key)->first();

        if ($object === null) {
            return null;
        }

        if ($path) {
            return data_get($object->parsedValue, $path);
        }

        return $object->parsedValue;
    }

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

    protected function casts(): array
    {
        return [
            'key' => UserSettings::class,
            'value' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
