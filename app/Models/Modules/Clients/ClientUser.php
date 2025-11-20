<?php

declare(strict_types=1);

namespace App\Models\Modules\Clients;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class ClientUser extends Pivot
{
    public $incrementing = false;

    protected $table = 'client_users';
}
