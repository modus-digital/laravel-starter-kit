<?php

namespace App\Models;

use App\Enums\Settings\UserSettings;
use App\Services\Validators\UserSettingsValidators;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    protected $primaryKey = ['user_id', 'key'];

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'key' => UserSettings::class,
        'value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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


    public function put(UserSettings $key, array $value): void
    {
        $validator = UserSettingsValidators::byKey($key, $value);

        if ($validator->fails()) {
            throw new \Exception('Invalid user setting value');
        }

        [$validatedKey, $validatedValue] = $validator->validated();

        $this->updateOrCreate(
            attributes: ['user_id' => $this->user_id, 'key' => $validatedKey],
            values: ['value' => json_encode($validatedValue)],
        );
    }

    public function get(UserSettings $key, ?string $field = null): array
    {
        $setting = $this->where('user_id', $this->user_id)->where('key', $key)->first();

        if ($field) {
            return data_get($setting, $field, []);
        }

        return $setting->parsedValue;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
        );
    }
}
