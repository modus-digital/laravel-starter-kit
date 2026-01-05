<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Clients\ClientUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasClients
{
    /**
     * @return BelongsToMany<Client, static, ClientUser, 'pivot'>
     */
    public function clients(): BelongsToMany
    {
        /** @var BelongsToMany<Client, User, ClientUser, 'pivot'> $relation */
        $relation = $this->belongsToMany(related: Client::class, table: 'client_users')
            ->using(class: ClientUser::class);

        return $relation;
    }

    // TODO: Add a method once ready to get the currently active client
}
