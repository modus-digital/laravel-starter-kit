<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Override;
use Database\Factories\UserFactory;
use App\Enums\Settings\Appearance;
use App\Enums\Settings\Language;
use App\Enums\Settings\Theme;
use App\Enums\Settings\TwoFactor;
use App\Enums\Settings\UserSettings;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Enums\RBAC\Permission;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;

    #[Override]
    protected static function booted(): void
    {
        static::created(function (User $user): void {
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
        'first_name',
        'last_name_prefix',
        'last_name',
        'email',
        'phone',
        'password',
        'last_login_at',
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
            'last_login_at' => 'datetime',
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
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn(): string => sprintf('%s %s %s', $this->first_name, $this?->last_name_prefix, $this?->last_name),
        );
    }

    #endregion

    #region Relationships

    /**
     * Get the user's sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(related: Session::class);
    }

    #endregion

    public function initials(): string
    {
        return Str::of($this->name)->explode(' ')->map(fn($name) => Str::of($name)->substr(0, 1))->implode('');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasPermissionTo(Permission::HAS_ACCESS_TO_ADMIN_PANEL);
    }
}
