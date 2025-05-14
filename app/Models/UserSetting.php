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

    public function updateValueAttribute(?string $path = null, mixed $newValue = null)
    {
        $currentValue = $this->value;

        if ($path !== null) {
            data_set($currentValue, $path, $newValue);
        } else {
            $currentValue = $newValue;
        }

        $validator = UserSettingsValidators::byKey(key: $this->key, data: $currentValue);

        if ($validator->fails()) {
            throw new \Exception('Invalid value provided for user setting: ' . $validator->errors()->first());
        }

        dump([
            'user_id' => $this->user_id,
            'key' => $this->key,
            'value' => json_encode($validator->validated()),
        ]);

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
        );
    }
}
