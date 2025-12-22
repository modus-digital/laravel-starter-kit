<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\Modules\Tasks\TaskView;
use App\Services\Modules\Tasks\MoveTaskInViewService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('syncs statuses by case-insensitive name and creates missing ones', function (): void {
    $client = Client::factory()->create();

    $view = TaskView::query()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'type' => 'list',
        'name' => 'My List',
    ]);

    $view->syncStatusesByNames([
        ['name' => 'todo'],
        ['name' => 'Backlog', 'color' => '#111111'],
    ]);

    $todo = TaskStatus::query()
        ->whereRaw('lower(name) = ?', ['todo'])
        ->first();

    expect($todo)->not->toBeNull();
    expect(TaskStatus::query()->where('name', 'Backlog')->exists())->toBeTrue();

    $statusIds = $view->statuses()->pluck('task_statuses.id');

    expect($statusIds)->toContain($todo?->id);
    expect($statusIds)->toContain(TaskStatus::query()->where('name', 'Backlog')->first()?->id);
});

it('removing a status from a view keeps the global status intact', function (): void {
    $client = Client::factory()->create();

    $view = TaskView::query()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'type' => 'list',
        'name' => 'List',
    ]);

    $view->syncStatusesByNames([
        ['name' => 'Todo'],
        ['name' => 'Backlog'],
    ]);

    $backlog = TaskStatus::query()->where('name', 'Backlog')->firstOrFail();

    $view->statuses()->detach($backlog->id);

    expect(TaskStatus::find($backlog->id))->not->toBeNull();
});

it('moves a task across columns and reorders positions per view', function (): void {
    $client = Client::factory()->create();

    $view = TaskView::query()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'type' => 'kanban',
        'name' => 'Board',
    ]);

    $view->syncStatusesByNames([
        ['name' => 'Todo'],
        ['name' => 'In Progress'],
    ]);

    $todo = TaskStatus::query()->where('name', 'Todo')->firstOrFail();
    $inProgress = TaskStatus::query()->where('name', 'In Progress')->firstOrFail();

    $taskA = Task::factory()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'status_id' => $todo->id,
    ]);

    $taskB = Task::factory()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'status_id' => $inProgress->id,
    ]);

    $service = new MoveTaskInViewService();

    $service->move(
        view: $view,
        task: $taskA,
        toStatus: $inProgress,
        toPosition: 0,
    );

    expect($taskA->refresh()->status_id)->toBe($inProgress->id);

    $inProgressOrder = $view->taskPositions()
        ->where('task_status_id', $inProgress->id)
        ->orderBy('position')
        ->pluck('task_id')
        ->all();

    $todoOrder = $view->taskPositions()
        ->where('task_status_id', $todo->id)
        ->orderBy('position')
        ->pluck('task_id')
        ->all();

    expect($inProgressOrder)->toBe([$taskA->id, $taskB->id]);
    expect($todoOrder)->toBe([]);
});
