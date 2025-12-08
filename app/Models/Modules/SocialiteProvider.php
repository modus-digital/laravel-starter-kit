<?php

declare(strict_types=1);

namespace App\Models\Modules;

use App\Enums\AuthenticationProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property AuthenticationProvider $provider
 * @property string|null $client_id
 * @property string|null $client_secret
 * @property string|null $redirect_uri
 * @property bool $is_enabled
 * @property int $sort_order
 * @property \Carbon\CarbonInterface|null $created_at
 * @property \Carbon\CarbonInterface|null $updated_at
 */
final class SocialiteProvider extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'is_enabled',
    ];

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    protected function casts(): array
    {
        return [
            'provider' => AuthenticationProvider::class,
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'client_secret' => 'encrypted',
        ];
    }
}
