<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('renders the tasks index page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    Task::factory()->for($client, 'taskable')->create();

    $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tasks/index')
            ->has('tasks', 1)
        );
});
