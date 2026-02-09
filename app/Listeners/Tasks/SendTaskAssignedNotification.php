<?php

declare(strict_types=1);

namespace App\Listeners\Tasks;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Tasks\TaskAssigned;
use App\Notifications\Tasks\TaskAssignedNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendTaskAssignedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(TaskAssigned $event): void
    {
        $user = $event->assignee;

        // Don't notify if user assigned task to themselves
        if ($user->id === $event->assigner->id) {
            return;
        }

        $preference = NotificationDeliveryMethod::tryFrom(
            (string) $user->getPreference('notifications.tasks', NotificationDeliveryMethod::EMAIL_PUSH->value)
        ) ?? NotificationDeliveryMethod::EMAIL_PUSH;

        $channels = $this->channelResolver->resolve($preference);

        if ($channels === []) {
            return;
        }

        $user->notify(new TaskAssignedNotification(
            task: $event->task,
            assigner: $event->assigner,
            channels: $channels,
        ));
    }
}
