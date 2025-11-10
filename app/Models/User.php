<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Permission;
use App\Traits\HasSettings;
use Carbon\CarbonInterface;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;
use ModusDigital\Clients\Traits\HasClients;
use Spatie\Permission\Traits\HasRoles;
use Str;

/**
 * The User model represents a user in the application.
 * It provides methods to access user settings, update them, and retrieve them.
 * This model is linked to the 'users' table which is used to store user information.
 *
 * @property-read string $id The unique identifier for the user
 * @property-read string $name The name of the user
 * @property-read string $email The email address of the user
 * @property-read string $password The password of the user
 * @property-read string|null $avatar_path The path to the user avatar
 * @property-read string|null $avatar_url The full URL to the user avatar
 * @property-read UserSetting $settings The user settings
 * @property-read CarbonInterface $email_verified_at The timestamp of the user email verification
 * @property-read CarbonInterface $created_at The timestamp of the user creation
 * @property-read CarbonInterface $updated_at The timestamp of the user update
 * @property-read string $initials Initials of the user
 * @property-read bool $can_access_panel Whether the user can access the admin panel
 */
final class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasClients;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasRoles;
    use HasSettings;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_path',
        'status',
        'provider',
        'email_verified_at',
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
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'avatar_url',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL);
    }

    public function initials(): string
    {
        return Str::of(string: $this->name)
            ->explode(' ')
            ->map(callback: fn (string $name): Stringable => Str::of(string: $name)->substr(start: 0, length: 1))
            ->implode(value: '');
    }

    /**
     * Get the user's avatar URL.
     *
     * @return Attribute<?string, never>
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
        );
    }

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
            'status' => ActivityStatus::class,
        ];
    }
}
