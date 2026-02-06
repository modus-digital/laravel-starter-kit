<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Database\Seeders\BootstrapApplicationSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->seed(BootstrapApplicationSeeder::class);
});

it('renders the tasks index page', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $client->users()->attach($user->id);

    Task::factory()->for($client, 'taskable')->create();

    $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('modules/tasks/index')
            ->has('tasks', 1)
        );
});

it('does not leak tasks when current client is not accessible', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    Task::factory()->for($client, 'taskable')->create();

    $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('modules/tasks/index')
            ->has('tasks', 0)
        );
});
