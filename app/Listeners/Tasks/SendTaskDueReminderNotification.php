<?php

declare(strict_types=1);

namespace App\Listeners\Tasks;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Tasks\TaskDueApproaching;
use App\Notifications\Tasks\TaskDueReminderNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskDueReminderNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(TaskDueApproaching $event): void
    {
        $usersToNotify = [];

        // Notify the assignee
        if ($event->task->assignedTo !== null) {
            $usersToNotify[] = $event->task->assignedTo;
        }

        // Notify the creator if different from assignee
        if ($event->task->createdBy !== null
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

            $user->notify(new TaskDueReminderNotification(
                task: $event->task,
                daysUntilDue: $event->daysUntilDue,
                channels: $channels,
            ));
        }
    }
}
