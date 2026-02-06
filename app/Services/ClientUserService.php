<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ActivityStatus;
use App\Models\Modules\Clients\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class ClientUserService
{
    /**
     * Add an existing user to a client
     */
    public function addUserToClient(Client $client, string $userId, ?int $roleId = null): User
    {
        $client->users()->attach($userId);

        $user = User::findOrFail($userId);

        if ($roleId !== null) {
            $role = Role::findOrFail($roleId);
            $user->syncRoles([$role->name]);
        }

        return $user;
    }

    /**
     * Create a new user and associate them with a client
     */
    public function createUserForClient(
        Client $client,
        string $name,
        string $email,
        string $password,
        ActivityStatus $status,
        ?int $roleId = null
    ): User {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => $status,
            'provider' => 'local',
        ]);

        if ($roleId !== null) {
            $role = Role::findOrFail($roleId);
            $user->syncRoles([$role->name]);
        }

        $client->users()->attach($user->id);

        return $user;
    }

    /**
     * Update a user's role
     */
    public function updateUserRole(User $user, int $roleId): void
    {
        $role = Role::findOrFail($roleId);
        $user->syncRoles([$role->name]);
    }

    /**
     * Remove a user from a client
     */
    public function removeUserFromClient(Client $client, User $user): void
    {
        $client->users()->detach($user->id);
    }
}
