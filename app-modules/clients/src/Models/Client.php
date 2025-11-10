<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Models;

use App\Enums\ActivityStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use ModusDigital\Clients\Database\Factories\ClientFactory;

final class Client extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'status',
        'website',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_user')
            ->using(ClientUser::class)
            ->withTimestamps();
    }

    protected static function newFactory(): Factory
    {
        return ClientFactory::new();
    }

    protected function casts(): array
    {
        return [
            'status' => ActivityStatus::class,
        ];
    }
}
