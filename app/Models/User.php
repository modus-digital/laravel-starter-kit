<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Settings\UserSettings;
use App\Enums\Settings\Appearance;
use App\Enums\Settings\Language;
use App\Enums\Settings\Theme;
use App\Enums\Settings\TwoFactor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public static function booted(): void
    {
        static::created(function (User $user) {
            $user->settings()->createMany([
                [
                    'key' => UserSettings::LOCALIZATION,
                    'value' => [
                        'locale' => Language::ENGLISH,
                        'timezone' => config('app.default_timezone', 'UTC'),
                        'date_format' => config('app.default_date_format', 'd-m-Y H:i'),
                    ],
                ],
                [
                    'key' => UserSettings::SECURITY,
                    'value' => [
                        'password_last_changed_at' => null,
                        'two_factor' => [
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
        });
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function settings(): HasMany
    {
        return $this->hasMany(
            related: UserSetting::class,
            foreignKey: 'user_id',
        );
    }

    /**
     * Get the user's initials based on their name
     * Extract only from the first and last word of their name
     *
     * @return string
     */
    public function initials(): string
    {
        $parts = Str::of($this->name)->explode(' ');

        if (count($parts) <= 1) {
            return Str::of($parts[0] ?? '')->substr(0, 1);
        }

        $firstName = $parts[0];
        $lastName = $parts[count($parts) - 1];

        return Str::of($firstName)->substr(0, 1) . Str::of($lastName)->substr(0, 1);
    }
}
