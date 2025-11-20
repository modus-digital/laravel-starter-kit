<?php

declare(strict_types=1);

namespace App\Models\Modules\SaaS;

use App\Enums\ActivityStatus;
use App\Models\Modules\Clients\Client;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\SubscriptionFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'plan_id',
        'status',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(related: Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(related: Plan::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(related: Invoice::class);
    }

    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
