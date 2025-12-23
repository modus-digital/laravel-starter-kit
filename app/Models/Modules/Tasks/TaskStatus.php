<?php

declare(strict_types=1);

namespace App\Models\Modules\Tasks;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * TaskStatus model definition
 *
 * @property string $id
 * @property string $name
 * @property string $color
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Task> $tasks
 */
final class TaskStatus extends Model
{
    /** @use HasFactory<\Database\Factories\Modules\Tasks\TaskStatusFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * Find an existing status by case-insensitive name, or create a new one.
     */
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
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(related: Task::class, foreignKey: 'status_id');
    }
}
