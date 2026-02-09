<?php

declare(strict_types=1);

namespace App\Listeners\Tasks;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Tasks\TaskCompleted;
use App\Notifications\Tasks\TaskCompletedNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskCompletedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(TaskCompleted $event): void
    {
        $usersToNotify = [];

        // Notify the assignee if different from the person who completed it
        if ($event->task->assignedTo !== null
            && $event->task->assignedTo->id !== $event->completedBy->id) {
            $usersToNotify[] = $event->task->assignedTo;
        }

        // Notify the creator if different from the person who completed it
        if ($event->task->createdBy !== null
            && $event->task->createdBy->id !== $event->completedBy->id
            && $event->task->createdBy->id !== $event->task->assignedTo?->id) {
            $usersToNotify[] = $event->task->createdBy;
        }

        foreach ($usersToNotify as $user) {
            $preference = NotificationDeliveryMethod::tryFrom(
                (string) $user->getPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)
            ) ?? NotificationDeliveryMethod::EMAIL_PUSH;

            $channels = $this->channelResolver->resolve($preference);

            if ($channels === []) {
                continue;
            }

            $user->notify(new TaskCompletedNotification(
                task: $event->task,
                completedBy: $event->completedBy,
                channels: $channels,
            ));
        }
    }
}
