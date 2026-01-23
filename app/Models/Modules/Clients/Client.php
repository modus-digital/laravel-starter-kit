<?php

declare(strict_types=1);

namespace App\Models\Modules\Clients;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Permission;
use App\Models\Activity;
use App\Models\User;
use App\Traits\HasTasks;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $contact_name
 * @property string $contact_email
 * @property string|null $contact_phone
 * @property string|null $address
 * @property string|null $postal_code
 * @property string|null $city
 * @property string|null $country
 * @property ActivityStatus $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read ClientBillingInfo|null $billingInfo
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Modules\SaaS\Subscription> $subscriptions
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Modules\SaaS\Invoice> $invoices
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Modules\Tasks\Task> $tasks
 */
final class Client extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Clients\ClientFactory> */
    use HasFactory;

    use HasTasks;
    use HasUuids;
    use Searchable;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address',
        'postal_code',
        'city',
        'country',
        'status',
    ];

    /**
     * Searchable columns for global search.
     *
     * @var array<int, string>
     */
    protected static array $searchable = ['name', 'contact_name', 'contact_email'];

    /**
     * Permission required to search this model.
     */
    protected static string $searchPermission = 'read:clients';

    /**
     * @return BelongsToMany<User, $this, ClientUser>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class, table: 'client_users')
            ->using(class: ClientUser::class);
    }

    /**
     * @return HasOne<ClientBillingInfo, $this>
     */
    public function billingInfo(): HasOne
    {
        return $this->hasOne(related: ClientBillingInfo::class);
    }

    /**
     * @return HasMany<\App\Models\Modules\SaaS\Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(related: \App\Models\Modules\SaaS\Subscription::class);
    }

    /**
     * @return HasMany<\App\Models\Modules\SaaS\Invoice, $this>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(related: \App\Models\Modules\SaaS\Invoice::class);
    }

    /**
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(related: Activity::class, name: 'subject');
    }

    /**
     * Get the subtitle for this search result.
     */
    public function getSearchResultSubtitle(): ?string
    {
        return $this->contact_email;
    }

    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
