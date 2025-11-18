<?php

namespace App\Models\Modules\Clients;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ClientUser extends Pivot
{
    public $incrementing = false;

    protected $table = 'client_users';
}
