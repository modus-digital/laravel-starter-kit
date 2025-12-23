<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\User;
use App\Services\TaskService;

it('returns only tasks accessible to the user (direct + member clients)', function (): void {
    $taskService = new TaskService();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $client = Client::factory()->create();
    $otherClient = Client::factory()->create();

    $client->users()->attach($user->id);

    $userTask = Task::factory()->for($user, 'taskable')->create();
    $clientTask = Task::factory()->for($client, 'taskable')->create();

    $otherUserTask = Task::factory()->for($otherUser, 'taskable')->create();
    $otherClientTask = Task::factory()->for($otherClient, 'taskable')->create();

    $accessibleTaskIds = $taskService
        ->getAccessibleTasksForUser($user)
        ->modelKeys();

    expect($accessibleTaskIds)
        ->toContain($userTask->id)
        ->toContain($clientTask->id)
        ->not->toContain($otherUserTask->id)
        ->not->toContain($otherClientTask->id);
});

it('scopes accessible tasks to the given current client id', function (): void {
    $taskService = new TaskService();

    $user = User::factory()->create();

    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();

    $clientA->users()->attach($user->id);
    $clientB->users()->attach($user->id);

    $taskA = Task::factory()->for($clientA, 'taskable')->create();
    $taskB = Task::factory()->for($clientB, 'taskable')->create();

    $scopedTaskIds = $taskService
        ->getAccessibleTasksForUser($user, $clientA->id)
        ->modelKeys();

    expect($scopedTaskIds)
        ->toContain($taskA->id)
        ->not->toContain($taskB->id);
});
