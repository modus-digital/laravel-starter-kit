<?php

declare(strict_types=1);

use App\Enums\NotificationDeliveryMethod;
use App\Events\Comments\CommentAdded;
use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('comment added event sends notification', function () {
    $creator = User::factory()->create();
    $assignee = User::factory()->create();
    $commenter = User::factory()->create();

    $assignee->setPreference('notifications.comments', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();
    $creator->setPreference('notifications.comments', NotificationDeliveryMethod::EMAIL_PUSH->value)->save();

    $task = Task::factory()->create([
        'created_by_id' => $creator->id,
        'assigned_to_id' => $assignee->id,
    ]);

    Event::dispatch(new CommentAdded(
        task: $task,
        commenter: $commenter,
        comment: ['type' => 'doc', 'content' => []],
    ));

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $assignee->id,
        'type' => 'App\Notifications\Tasks\CommentAddedNotification',
    ]);

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $creator->id,
        'type' => 'App\Notifications\Tasks\CommentAddedNotification',
    ]);
});

test('comment notification respects user preferences', function () {
    $creator = User::factory()->create();
    $commenter = User::factory()->create();

    $creator->setPreference('notifications.comments', NotificationDeliveryMethod::NONE->value)->save();

    $task = Task::factory()->create([
        'created_by_id' => $creator->id,
    ]);

    Event::dispatch(new CommentAdded(
        task: $task,
        commenter: $commenter,
        comment: ['type' => 'doc', 'content' => []],
    ));

    $this->assertDatabaseMissing('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $creator->id,
        'type' => 'App\Notifications\Tasks\CommentAddedNotification',
    ]);
});
