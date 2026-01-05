<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property string|null $icon
 * @property string|null $color
 */
final class Role extends SpatieRole
{
    /**
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(related: Activity::class, name: 'subject');
    }
}
