<?php

declare(strict_types=1);

namespace App\Models\Modules\Tasks;

use App\Enums\Modules\Tasks\TaskViewType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Task view model definition
 *
 * @property string $id
 * @property string $taskable_type
 * @property string $taskable_id
 * @property bool $is_default
 * @property string $name
 * @property string $slug
 * @property TaskViewType $type
 * @property array|null $metadata
 * @property-read Model $taskable
 * @property-read Collection<int, TaskStatus> $statuses
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
        'taskable_type',
        'taskable_id',
        'is_default',
        'name',
        'slug',
        'type',
        'metadata',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Statuses (columns) enabled for this view.
     *
     * @return BelongsToMany<TaskStatus, $this>
     */
    public function statuses(): BelongsToMany
    {
        return $this->belongsToMany(
            related: TaskStatus::class,
            table: 'task_view_statuses',
            foreignPivotKey: 'task_view_id',
            relatedPivotKey: 'task_status_id',
        )->withPivot('position')->orderByPivot('position');
    }

    /**
     * Tasks for this view's taskable, filtered to only those whose status is enabled in this view.
     *
     * @return Builder<Task>
     */
    public function tasks(): Builder
    {
        $enabledStatusIds = $this->statuses()->pluck('task_statuses.id');

        return Task::query()
            ->where('taskable_type', $this->taskable_type)
            ->where('taskable_id', $this->taskable_id)
            ->whereIn('status_id', $enabledStatusIds)
            ->orderBy('status_id')
            ->orderBy('order')
            ->orderBy('created_at');
    }

    /**
     * Sync statuses by name (case-insensitive). Creates missing statuses.
     *
     * @param  array<int, array{name: string, color?: string}>  $statuses
     */
    public function syncStatusesByNames(array $statuses): void
    {
        $syncData = [];

        foreach ($statuses as $position => $statusData) {
            $status = TaskStatus::findOrCreateByName(
                name: $statusData['name'],
                color: $statusData['color'] ?? null,
            );

            $syncData[$status->id] = ['position' => $position];
        }

        $this->statuses()->sync($syncData);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TaskViewType::class,
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
