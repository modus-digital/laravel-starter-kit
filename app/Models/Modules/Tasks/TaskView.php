<?php

declare(strict_types=1);

namespace App\Models\Modules\Tasks;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $taskable_id
 * @property string $taskable_type
 * @property string $type
 * @property string $name
 * @property bool $is_default
 */
final class TaskView extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Tasks\TaskViewFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'taskable_id',
        'taskable_type',
        'type',
        'name',
        'created_by_id',
        'is_default',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'created_by_id');
    }

    /**
     * @return BelongsToMany<TaskStatus, $this>
     */
    public function statuses(): BelongsToMany
    {
        return $this->belongsToMany(related: TaskStatus::class, table: 'task_view_statuses')
            ->withPivot(['sort_order', 'wip_limit'])
            ->withTimestamps()
            ->orderBy('task_view_statuses.sort_order');
    }

    /**
     * @return HasMany<TaskViewTaskPosition, $this>
     */
    public function taskPositions(): HasMany
    {
        return $this->hasMany(related: TaskViewTaskPosition::class);
    }

    /**
     * Sync statuses for the view using case-insensitive names.
     *
     * @param  array<int, array{name:string,color?:string,sort_order?:int,wip_limit?:int|null}>  $definitions
     */
    public function syncStatusesByNames(array $definitions): void
    {
        $syncData = [];

        foreach ($definitions as $index => $definition) {
            $status = TaskStatus::findOrCreateByName(
                name: $definition['name'],
                color: $definition['color'] ?? null,
            );

            $syncData[$status->id] = [
                'sort_order' => $definition['sort_order'] ?? $index,
                'wip_limit' => $definition['wip_limit'] ?? null,
            ];
        }

        $this->statuses()->sync($syncData);
    }
}
