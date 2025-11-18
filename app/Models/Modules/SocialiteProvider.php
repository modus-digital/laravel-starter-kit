<?php

declare(strict_types=1);

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AuthenticationProvider;

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
    protected $table = 'oauth_providers';

    protected $fillable = [
        'client_id',
        'client_secret',
        'redirect_uri',
        'is_enabled',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered($query)
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