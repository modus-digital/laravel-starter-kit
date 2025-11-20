<?php

declare(strict_types=1);

namespace App\Models\Modules\SaaS;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\PlanFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'price',
        'interval',
        'trial_days',
        'features',
        'status',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(related: Subscription::class);
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
