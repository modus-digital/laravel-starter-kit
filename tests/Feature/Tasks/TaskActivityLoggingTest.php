<?php

declare(strict_types=1);

use App\Enums\Modules\Tasks\TaskPriority;
use App\Models\Activity;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\User;
use App\Services\TaskService;
use Database\Seeders\BootstrapApplicationSeeder;

beforeEach(function (): void {
    $this->seed(BootstrapApplicationSeeder::class);
    $this->taskService = new TaskService();
});

it('logs only changed fields when updating a task', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'title' => 'Original Title',
        'priority' => TaskPriority::NORMAL,
    ]);

    $initialActivityCount = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->count();

    $this->taskService->updateTask($user, $task, [
        'title' => 'Updated Title',
        'priority' => TaskPriority::HIGH,
    ]);

    $activities = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'like', 'tasks.%')
        ->orderBy('created_at')
        ->get();

    expect($activities->count())->toBe($initialActivityCount + 2);

    $titleActivity = $activities->where('event', 'tasks.title_changed')->first();
    expect($titleActivity)->not->toBeNull();
    expect($titleActivity->properties->get('old'))->toBe('Original Title');
    expect($titleActivity->properties->get('new'))->toBe('Updated Title');

    $priorityActivity = $activities->where('event', 'tasks.priority_changed')->first();
    expect($priorityActivity)->not->toBeNull();
    expect($priorityActivity->properties->get('field'))->toBe('priority');
});

it('does not log unchanged fields', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'title' => 'My Task',
        'priority' => TaskPriority::NORMAL,
    ]);

    $initialActivityCount = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->count();

    // Update with same values
    $this->taskService->updateTask($user, $task, [
        'title' => 'My Task', // Same value
        'priority' => TaskPriority::NORMAL->value, // Same value
    ]);

    $newActivityCount = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->count();

    expect($newActivityCount)->toBe($initialActivityCount);
});

it('logs status changes with status details', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create();

    $oldStatus = $task->status;
    $newStatus = TaskStatus::firstOrCreate(
        ['name' => 'In Progress'],
        ['color' => '#3498db']
    );
    // Refresh to get the actual color if status already existed
    $newStatus->refresh();

    $this->taskService->updateTask($user, $task, [
        'status_id' => $newStatus->id,
    ]);

    $activity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.status_changed')
        ->first();

    expect($activity)->not->toBeNull();
    $oldValue = $activity->properties->get('old');
    $newValue = $activity->properties->get('new');

    expect($oldValue)->toBeArray();
    expect($oldValue['name'])->toBe($oldStatus->name);
    expect($oldValue['color'])->toBe($oldStatus->color);

    expect($newValue)->toBeArray();
    expect($newValue['name'])->toBe('In Progress');
    expect($newValue['color'])->toBe($newStatus->color);
});

it('logs priority changes with formatted values', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'priority' => TaskPriority::LOW,
    ]);

    $this->taskService->updateTask($user, $task, [
        'priority' => TaskPriority::CRITICAL->value,
    ]);

    $activity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.priority_changed')
        ->first();

    expect($activity)->not->toBeNull();
    $oldValue = $activity->properties->get('old');
    $newValue = $activity->properties->get('new');

    expect($oldValue)->toBeArray();
    expect($oldValue['value'])->toBe('low');
    expect($oldValue['label'])->toBe('Low');

    expect($newValue)->toBeArray();
    expect($newValue['value'])->toBe('critical');
    expect($newValue['label'])->toBe('Critical');
});

it('logs assignment changes correctly', function (): void {
    $user = User::factory()->create();
    $assignedUser = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'assigned_to_id' => null,
    ]);

    // Assign task
    $this->taskService->updateTask($user, $task, [
        'assigned_to_id' => $assignedUser->id,
    ]);

    $assignActivity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.assigned')
        ->first();

    expect($assignActivity)->not->toBeNull();
    $newValue = $assignActivity->properties->get('new');
    expect($newValue)->toBeArray();
    expect($newValue['name'])->toBe($assignedUser->name);

    // Reassign task
    $otherUser = User::factory()->create();
    $this->taskService->updateTask($user, $task, [
        'assigned_to_id' => $otherUser->id,
    ]);

    $reassignActivity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.reassigned')
        ->first();

    expect($reassignActivity)->not->toBeNull();
    expect($reassignActivity->properties->has('old'))->toBeTrue();
    expect($reassignActivity->properties->has('new'))->toBeTrue();

    // Unassign task
    $this->taskService->updateTask($user, $task, [
        'assigned_to_id' => '',
    ]);

    $unassignActivity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.unassigned')
        ->first();

    expect($unassignActivity)->not->toBeNull();
    expect($unassignActivity->properties->has('old'))->toBeTrue();
    expect($unassignActivity->properties->has('new'))->toBeFalse();
});

it('logs due date changes correctly', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'due_date' => null,
    ]);

    // Set due date
    $this->taskService->updateTask($user, $task, [
        'due_date' => '2024-12-31',
    ]);

    $setActivity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.due_date_set')
        ->first();

    expect($setActivity)->not->toBeNull();
    expect($setActivity->properties->has('old'))->toBeFalse();
    expect($setActivity->properties->has('new'))->toBeTrue();

    // Change due date
    $this->taskService->updateTask($user, $task, [
        'due_date' => '2025-01-15',
    ]);

    $changeActivity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.due_date_changed')
        ->orderBy('created_at', 'desc')
        ->first();

    expect($changeActivity)->not->toBeNull();
    expect($changeActivity->properties->has('old'))->toBeTrue();
    expect($changeActivity->properties->has('new'))->toBeTrue();

    // Remove due date
    $this->taskService->updateTask($user, $task, [
        'due_date' => null,
    ]);

    $removeActivity = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->where('event', 'tasks.due_date_removed')
        ->first();

    expect($removeActivity)->not->toBeNull();
    expect($removeActivity->properties->has('old'))->toBeTrue();
    expect($removeActivity->properties->has('new'))->toBeFalse();
});

it('uses correct translation keys for activity descriptions', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create();

    $this->taskService->updateTask($user, $task, [
        'title' => 'New Title',
        'priority' => TaskPriority::HIGH->value,
    ]);

    $activities = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->whereIn('event', ['tasks.title_changed', 'tasks.priority_changed'])
        ->get();

    expect($activities->count())->toBe(2);

    foreach ($activities as $activity) {
        expect($activity->description)->toStartWith('activity.');
        expect($activity->description)->toBe("activity.{$activity->event}");
    }
});

it('logs multiple field changes as separate activities', function (): void {
    $user = User::factory()->create();
    $task = Task::factory()->for($user, 'taskable')->create([
        'title' => 'Original',
        'description' => 'Old description',
        'priority' => TaskPriority::LOW,
    ]);

    $initialCount = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->count();

    $this->taskService->updateTask($user, $task, [
        'title' => 'New Title',
        'description' => 'New description',
        'priority' => TaskPriority::CRITICAL->value,
    ]);

    $newCount = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->count();

    expect($newCount)->toBe($initialCount + 3);

    $events = Activity::query()
        ->where('subject_type', Task::class)
        ->where('subject_id', $task->id)
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->pluck('event')
        ->toArray();

    expect($events)->toContain('tasks.title_changed');
    expect($events)->toContain('tasks.description_changed');
    expect($events)->toContain('tasks.priority_changed');
});
