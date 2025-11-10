<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use ModusDigital\Clients\Models\Client;

trait HasClients
{
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_user');
    }
}
