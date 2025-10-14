<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\Settings\Appearance;
use App\Enums\Settings\Language;
use App\Enums\Settings\Theme;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSettings
{
    /**
     * @return HasMany<UserSetting, $this>
     */
    public function settings(): HasMany
    {
        return $this->hasMany(
            related: UserSetting::class,
            foreignKey: 'user_id'
        );
    }

    protected static function booted(): void
    {
        static::created(
            callback: function (User $user): void {
                $user->settings()->createMany([
                    [
                        'key' => UserSettings::LOCALIZATION,
                        'value' => [
                            'locale' => Language::ENGLISH,
                            'timezone' => 'Europe/Amsterdam',
                            'date_format' => 'd-m-Y',
                            'time_format' => 'H:i',
                        ],
                    ],
                    [
                        'key' => UserSettings::SECURITY,
                        'value' => [
                            'password_last_changed_at' => null,
                            'two_factor' => [
                                'provider' => null,
                                'status' => TwoFactor::DISABLED,
                                'secret' => null,
                                'confirmed_at' => null,
                                'recovery_codes' => [],
                            ],
                        ],
                    ],
                    [
                        'key' => UserSettings::DISPLAY,
                        'value' => [
                            'appearance' => Appearance::SYSTEM,
                            'theme' => Theme::BLUE,
                        ],
                    ],
                ]);
            }
        );
    }
}
