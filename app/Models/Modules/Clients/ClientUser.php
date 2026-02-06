<?php

declare(strict_types=1);

namespace App\Models\Modules\Clients;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property string $client_id
 * @property string $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
final class ClientUser extends Pivot
{
    public $incrementing = false;

    protected $table = 'client_users';
}
