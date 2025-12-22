<?php

declare(strict_types=1);

namespace App\Models\Modules\Tasks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $task_view_id
 * @property string $task_status_id
 * @property string $task_id
 * @property int $position
 */
final class TaskViewTaskPosition extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Tasks\TaskViewTaskPositionFactory> */
    use HasFactory;

    protected $fillable = [
        'task_view_id',
        'task_status_id',
        'task_id',
        'position',
    ];

    /**
     * @return BelongsTo<TaskView, $this>
     */
    public function view(): BelongsTo
    {
        return $this->belongsTo(related: TaskView::class, foreignKey: 'task_view_id');
    }

    /**
     * @return BelongsTo<TaskStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(related: TaskStatus::class, foreignKey: 'task_status_id');
    }

    /**
     * @return BelongsTo<Task, $this>
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(related: Task::class, foreignKey: 'task_id');
    }
}
