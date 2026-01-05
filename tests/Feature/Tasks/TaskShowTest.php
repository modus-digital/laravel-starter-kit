<?php

declare(strict_types=1);

use App\Models\Activity;
use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('shows an accessible task with statuses and activities', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'title' => 'My Task',
        'description' => 'Task description',
    ]);

    // Use existing statuses or create unique ones
    $statuses = TaskStatus::all();
    if ($statuses->count() < 3) {
        TaskStatus::factory()->count(3 - $statuses->count())->create();
        $statuses = TaskStatus::all();
    }

    // Create some activities for this task
    Activity::factory()->create([
        'log_name' => 'tasks',
        'subject_type' => Task::class,
        'subject_id' => $task->id,
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'event' => 'tasks.created',
        'description' => 'Task created',
    ]);

    Activity::factory()->create([
        'log_name' => 'tasks',
        'subject_type' => Task::class,
        'subject_id' => $task->id,
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'event' => 'tasks.updated',
        'description' => 'Task updated',
    ]);

    $response = $this->actingAs($user)
        ->get(route('tasks.show', $task));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('tasks/show')
        ->where('task.id', $task->id)
        ->where('task.title', 'My Task')
        ->where('task.description', 'Task description')
        ->has('statuses')
        ->has('activities', 2)
    );
});

it('forbids showing a task the user cannot access', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $task = Task::factory()->for($otherUser, 'taskable')->create();

    $this->actingAs($user)
        ->get(route('tasks.show', $task))
        ->assertForbidden();
});

it('shows client-scoped task when user is a member of the client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();
    $client->users()->attach($user->id);

    $task = Task::factory()->for($client, 'taskable')->create([
        'title' => 'Client Task',
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->get(route('tasks.show', $task));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('tasks/show')
        ->where('task.id', $task->id)
        ->where('task.title', 'Client Task')
        ->has('statuses')
    );
});

it('forbids showing a client-scoped task when user is not a member', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $task = Task::factory()->for($client, 'taskable')->create();

    $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->get(route('tasks.show', $task))
        ->assertForbidden();
});

it('only shows activities for the specific task', function (): void {
    $user = User::factory()->create();
    $task1 = Task::factory()->for($user, 'taskable')->create();
    $task2 = Task::factory()->for($user, 'taskable')->create();

    // Create activities for both tasks
    Activity::factory()->create([
        'log_name' => 'tasks',
        'subject_type' => Task::class,
        'subject_id' => $task1->id,
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'event' => 'tasks.created',
    ]);

    Activity::factory()->create([
        'log_name' => 'tasks',
        'subject_type' => Task::class,
        'subject_id' => $task2->id,
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'event' => 'tasks.created',
    ]);

    $response = $this->actingAs($user)
        ->get(route('tasks.show', $task1));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->has('activities', 1)
        ->where('activities.0.subject_id', (string) $task1->id)
    );
});

it('requires authentication to show a task', function (): void {
    $task = Task::factory()->create();

    $this->get(route('tasks.show', $task))
        ->assertRedirect(route('login'));
});
