<?php

declare(strict_types=1);

namespace App\Models\Modules\Tasks;

use App\Enums\Modules\Tasks\TaskPriority;
use App\Enums\Modules\Tasks\TaskType;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Task model definition
 *
 * @property string $id
 * @property string $taskable_id
 * @property string $taskable_type
 * @property string $title
 * @property string|null $description
 * @property TaskType $type
 * @property TaskPriority $priority
 * @property string $status_id
 * @property int|null $order
 * @property \Carbon\Carbon|null $due_date
 * @property \Carbon\Carbon|null $completed_at
 * @property string|null $created_by_id
 * @property string|null $assigned_to_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read Model $taskable
 * @property-read TaskStatus $status
 * @property-read User|null $createdBy
 * @property-read User|null $assignedTo
 */
final class Task extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Tasks\TaskFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'taskable_type',
        'taskable_id',
        'title',
        'description',
        'type',
        'priority',
        'status_id',
        'order',
        'due_date',
        'completed_at',
        'created_by_id',
        'assigned_to_id',
    ];

    /**
     * @return BelongsTo<TaskStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(related: TaskStatus::class, foreignKey: 'status_id');
    }

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
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(related: User::class, foreignKey: 'assigned_to_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TaskType::class,
            'priority' => TaskPriority::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
