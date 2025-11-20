<?php

declare(strict_types=1);

namespace App\Models\Modules\Clients;

use App\Enums\ActivityStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Client extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Clients\ClientFactory> */
    use HasFactory;

    use HasUuids;
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(related: User::class, table: 'client_users')
            ->using(class: ClientUser::class);
    }

    public function billingInfo(): HasOne
    {
        return $this->hasOne(related: ClientBillingInfo::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(related: \App\Models\Modules\SaaS\Subscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(related: \App\Models\Modules\SaaS\Invoice::class);
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
