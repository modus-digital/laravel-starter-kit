<?php

declare(strict_types=1);

namespace App\Listeners\Tasks;

use App\Enums\NotificationDeliveryMethod;
use App\Events\Comments\CommentAdded;
use App\Notifications\Tasks\CommentAddedNotification;
use App\Services\NotificationChannelResolver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class SendCommentAddedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private readonly NotificationChannelResolver $channelResolver,
    ) {}

    public function handle(CommentAdded $event): void
    {
        $usersToNotify = [];

        // Notify the assignee (including if they're the commenter, for activity tracking)
        if ($event->task->assignedTo !== null) {
            $usersToNotify[] = $event->task->assignedTo;
        }

        // Notify the creator if different from the assignee
        if ($event->task->createdBy !== null
            && $event->task->createdBy->id !== $event->task->assignedTo?->id) {
            $usersToNotify[] = $event->task->createdBy;
        }

        foreach ($usersToNotify as $user) {
            $preference = NotificationDeliveryMethod::tryFrom(
                (string) $user->getPreference('notifications.comments', NotificationDeliveryMethod::EMAIL_PUSH->value)
            ) ?? NotificationDeliveryMethod::EMAIL_PUSH;

            $channels = $this->channelResolver->resolve($preference);

            if ($channels === []) {
                continue;
            }

            $user->notify(new CommentAddedNotification(
                task: $event->task,
                commenter: $event->commenter,
                comment: $event->comment,
                channels: $channels,
            ));
        }
    }
}
