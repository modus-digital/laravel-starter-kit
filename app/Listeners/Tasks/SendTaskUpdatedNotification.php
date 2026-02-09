<?php

declare(strict_types=1);

namespace App\Listeners\Tasks;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Tasks\TaskUpdated;
use App\Notifications\Tasks\TaskUpdatedNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(TaskUpdated $event): void
    {
        $usersToNotify = [];

        // Notify the assignee if different from the person who updated it
        if ($event->task->assignedTo !== null
            && $event->task->assignedTo->id !== $event->updatedBy->id) {
            $usersToNotify[] = $event->task->assignedTo;
        }

        // Notify the creator if different from the person who updated it
        if ($event->task->createdBy !== null
            && $event->task->createdBy->id !== $event->updatedBy->id
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

            $user->notify(new TaskUpdatedNotification(
                task: $event->task,
                updatedBy: $event->updatedBy,
                changes: $event->changes,
                channels: $channels,
            ));
        }
    }
}
