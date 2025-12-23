<?php

declare(strict_types=1);

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\Modules\Tasks\TaskView;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('syncs statuses by case-insensitive name and creates missing ones', function (): void {
    $client = Client::factory()->create();

    $view = TaskView::query()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'type' => 'list',
        'name' => 'My List',
        'slug' => 'my-list',
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
        'slug' => 'list',
    ]);

    $view->syncStatusesByNames([
        ['name' => 'Todo'],
        ['name' => 'Backlog'],
    ]);

    $backlog = TaskStatus::query()->where('name', 'Backlog')->firstOrFail();

    $view->statuses()->detach($backlog->id);

    expect(TaskStatus::find($backlog->id))->not->toBeNull();
});

it('hides tasks whose status is not enabled in the view', function (): void {
    $client = Client::factory()->create();

    $view = TaskView::query()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'type' => 'kanban',
        'name' => 'Board',
        'slug' => 'board',
    ]);

    $view->syncStatusesByNames([
        ['name' => 'Todo'],
    ]);

    $todo = TaskStatus::query()->where('name', 'Todo')->firstOrFail();
    $hidden = TaskStatus::findOrCreateByName('Hidden');

    $visibleTask = Task::factory()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'status_id' => $todo->id,
    ]);

    $hiddenTask = Task::factory()->create([
        'taskable_id' => $client->id,
        'taskable_type' => $client::class,
        'status_id' => $hidden->id,
    ]);

    $viewTasks = $view->tasks()->get();

    expect($viewTasks)->toHaveCount(1);
    expect($viewTasks->first()->id)->toBe($visibleTask->id);
});
