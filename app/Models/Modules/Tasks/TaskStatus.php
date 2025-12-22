<?php

declare(strict_types=1);

namespace App\Models\Modules\Tasks;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model definition
 *
 * @property-read string $id
 * @property string $name
 * @property string $color
 * @property \Illuminate\Database\Eloquent\Collection<int, Task> $tasks
 */
final class TaskStatus extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Tasks\TaskStatusFactory> */
    use HasFactory;

    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'color',
    ];

    public static function findOrCreateByName(string $name, ?string $color = null): self
    {
        $existing = self::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return self::query()->create([
            'name' => $name,
            'color' => $color ?? '#3498db',
        ]);
    }

    /**
     * @return BelongsToMany<TaskView, $this>
     */
    public function views(): BelongsToMany
    {
        return $this->belongsToMany(related: TaskView::class, table: 'task_view_statuses')
            ->withPivot(['sort_order', 'wip_limit'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(related: Task::class, foreignKey: 'status_id');
    }
}
