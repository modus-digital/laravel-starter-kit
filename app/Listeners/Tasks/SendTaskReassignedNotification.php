<?php

declare(strict_types=1);

namespace App\Listeners\Tasks;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Tasks\TaskReassigned;
use App\Notifications\Tasks\TaskReassignedNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskReassignedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(TaskReassigned $event): void
    {
        $usersToNotify = [];

        // Notify the new assignee if different from reassigner
        if ($event->newAssignee instanceof \App\Models\User && $event->newAssignee->id !== $event->reassigner->id) {
            $usersToNotify[] = $event->newAssignee;
        }

        // Notify the previous assignee if different from reassigner and new assignee
        if ($event->previousAssignee instanceof \App\Models\User
            && $event->previousAssignee->id !== $event->reassigner->id
            && $event->previousAssignee->id !== $event->newAssignee?->id) {
            $usersToNotify[] = $event->previousAssignee;
        }

        foreach ($usersToNotify as $user) {
            $preference = NotificationDeliveryMethod::tryFrom(
                (string) $user->getPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)
            ) ?? NotificationDeliveryMethod::EMAIL_PUSH;

            $channels = $this->channelResolver->resolve($preference);

            if ($channels === []) {
                continue;
            }

            $user->notify(new TaskReassignedNotification(
                task: $event->task,
                previousAssignee: $event->previousAssignee,
                newAssignee: $event->newAssignee,
                reassigner: $event->reassigner,
                channels: $channels,
            ));
        }
    }
}
