<?php

declare(strict_types=1);

use App\Enums\NotificationDeliveryMethod;
use App\Events\Tasks\TaskAssigned;
use App\Events\Tasks\TaskCompleted;
use App\Events\Tasks\TaskReassigned;
use App\Events\Tasks\TaskUpdated;
use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\User;
use App\Notifications\Tasks\TaskAssignedNotification;
use Database\Seeders\BootstrapApplicationSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(BootstrapApplicationSeeder::class);
});

test('task assigned event sends notification to assignee', function () {
    Notification::fake();

    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $assignee->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();

    $task = Task::factory()->create([
        'created_by_id' => $assigner->id,
        'assigned_to_id' => $assignee->id,
    ]);

    Event::dispatch(new TaskAssigned(
        task: $task,
        assignee: $assignee,
        assigner: $assigner,
    ));

    // Assert notification was sent to assignee
    Notification::assertSentTo($assignee, TaskAssignedNotification::class);

    // Assert notification was sent exactly once (not duplicated)
    Notification::assertCount(1);
});

test('task assigned notification respects user preferences', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $assignee->setPreference('notifications.tasks', NotificationDeliveryMethod::NONE->value)->save();

    $task = Task::factory()->create([
        'created_by_id' => $assigner->id,
        'assigned_to_id' => $assignee->id,
    ]);

    Event::dispatch(new TaskAssigned(
        task: $task,
        assignee: $assignee,
        assigner: $assigner,
    ));

    $this->assertDatabaseMissing('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $assignee->id,
        'type' => 'App\Notifications\Tasks\TaskAssignedNotification',
    ]);
});

test('task completed event sends notification', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $completedBy = User::factory()->create();

    $assignee->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();
    $creator->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();

    $status = TaskStatus::findOrCreateByName('Done');
    $task = Task::factory()->create([
        'created_by_id' => $creator->id,
        'assigned_to_id' => $assignee->id,
        'status_id' => $status->id,
        'completed_at' => now(),
    ]);

    Event::dispatch(new TaskCompleted(
        task: $task,
        completedBy: $completedBy,
    ));

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $assignee->id,
        'type' => 'App\Notifications\Tasks\TaskCompletedNotification',
    ]);

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $creator->id,
        'type' => 'App\Notifications\Tasks\TaskCompletedNotification',
    ]);
});

test('task reassigned event sends notification to both assignees', function () {
    $reassigner = User::factory()->create();
    $previousAssignee = User::factory()->create();
    $newAssignee = User::factory()->create();

    $previousAssignee->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();
    $newAssignee->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();

    $task = Task::factory()->create([
        'assigned_to_id' => $newAssignee->id,
    ]);

    Event::dispatch(new TaskReassigned(
        task: $task,
        previousAssignee: $previousAssignee,
        newAssignee: $newAssignee,
        reassigner: $reassigner,
    ));

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $newAssignee->id,
        'type' => 'App\Notifications\Tasks\TaskReassignedNotification',
    ]);

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $previousAssignee->id,
        'type' => 'App\Notifications\Tasks\TaskReassignedNotification',
    ]);
});

test('task updated event sends notification', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $updatedBy = User::factory()->create();

    $assignee->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();
    $creator->setPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();

    $task = Task::factory()->create([
        'created_by_id' => $creator->id,
        'assigned_to_id' => $assignee->id,
    ]);

    Event::dispatch(new TaskUpdated(
        task: $task,
        updatedBy: $updatedBy,
        changes: ['title' => ['old' => 'Old Title', 'new' => 'New Title']],
    ));

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $assignee->id,
        'type' => 'App\Notifications\Tasks\TaskUpdatedNotification',
    ]);
});
