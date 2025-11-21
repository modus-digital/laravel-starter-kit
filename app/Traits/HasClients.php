<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Clients\ClientUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasClients
{
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(related: Client::class, table: 'client_users')
            ->using(class: ClientUser::class);
    }

    // TODO: Add a method once ready to get the currently active client
}
