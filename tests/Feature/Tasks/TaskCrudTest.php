<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\User;
use Database\Seeders\BootstrapApplicationSeeder;

beforeEach(function (): void {
    $this->seed(BootstrapApplicationSeeder::class);
});

it('creates a user-scoped task when no client is selected', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('tasks.store'), [
            'title' => 'My Task',
        ])
        ->assertRedirect();

    $task = Task::query()->firstOrFail();

    expect($task->taskable_type)->toBe(User::class);
    expect($task->taskable_id)->toBe($user->id);
    expect($task->created_by_id)->toBe($user->id);

    $todo = TaskStatus::query()->whereRaw('lower(name) = ?', ['todo'])->first();
    expect($todo)->not->toBeNull();
    expect($task->status_id)->toBe($todo?->id);
});

it('creates a client-scoped task when a client is selected and user is a member', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();
    $client->users()->attach($user->id);

    $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->post(route('tasks.store'), [
            'title' => 'Client Task',
        ])
        ->assertRedirect();

    $task = Task::query()->firstOrFail();

    expect($task->taskable_type)->toBe(Client::class);
    expect($task->taskable_id)->toBe($client->id);
    expect($task->created_by_id)->toBe($user->id);
});

it('forbids creating a client-scoped task when user is not a member of the selected client', function (): void {
    $user = User::factory()->create();
    $client = Client::factory()->create();

    $this->actingAs($user)
        ->withSession(['current_client_id' => $client->id])
        ->post(route('tasks.store'), [
            'title' => 'Hacked',
        ])
        ->assertForbidden();
});

it('updates an accessible task and sets completed_at when moving to Done', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'completed_at' => null,
    ]);

    $done = TaskStatus::firstOrCreate(
        ['name' => 'Done'],
        ['color' => '#2ecc71'],
    );

    $this->actingAs($user)
        ->patch(route('tasks.update', $task), [
            'title' => 'Updated Title',
            'status_id' => $done->id,
        ])
        ->assertRedirect();

    $task->refresh();

    expect($task->title)->toBe('Updated Title');
    expect($task->status_id)->toBe($done->id);
    expect($task->completed_at)->not->toBeNull();
});

it('forbids updating a task the user cannot access', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $task = Task::factory()->for($otherUser, 'taskable')->create();

    $this->actingAs($user)
        ->patch(route('tasks.update', $task), [
            'title' => 'Hacked',
        ])
        ->assertForbidden();
});

it('deletes an accessible task (soft delete)', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create();

    $this->actingAs($user)
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    expect(Task::find($task->id))->toBeNull();
    expect(Task::withTrashed()->find($task->id))->not->toBeNull();
});
