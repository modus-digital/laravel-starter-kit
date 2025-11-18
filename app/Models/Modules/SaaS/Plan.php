<?php

namespace App\Models\Modules\SaaS;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\SaaS\PlanFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'price',
        'interval',
        'trial_days',
        'features',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(related: Subscription::class);
    }
}
