<?php

declare(strict_types=1);

namespace App\Notifications\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

final class TaskUpdatedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        private readonly Task $task,
        private readonly User $updatedBy,
        private readonly array $changes,
        private readonly array $channels,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        $changedFields = implode(', ', array_keys($this->changes));

        return (new MailMessage)
            ->subject(__('notifications.tasks.updated.subject', ['title' => $this->task->title]))
            ->greeting(__('notifications.tasks.updated.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.tasks.updated.line1', [
                'updatedBy' => $this->updatedBy->name,
                'title' => $this->task->title,
            ]))
            ->line(__('notifications.tasks.updated.line2', ['fields' => $changedFields]))
            ->action(__('notifications.tasks.updated.action'), route('tasks.show', $this->task))
            ->line(__('notifications.tasks.updated.line3'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $changedFields = implode(', ', array_keys($this->changes));
        $titleKey = 'notifications.tasks.updated.title';
        $bodyKey = 'notifications.tasks.updated.body';
        $replacements = [
            'updatedBy' => $this->updatedBy->name,
            'title' => $this->task->title,
            'fields' => $changedFields,
        ];

        return [
            'title' => __($titleKey, [
                'updatedBy' => $this->updatedBy->name,
                'title' => $this->task->title,
            ]),
            'body' => __($bodyKey, $replacements),
            'action_url' => route('tasks.show', $this->task),
            // Store translation key and replacements for frontend translation if needed
            'translation_key' => $titleKey,
            'translation_replacements' => $replacements,
            'context' => [
                'type' => 'task',
                'task_title' => $this->task->title,
                'task_description' => Str::limit(strip_tags($this->task->description ?? ''), 200),
                'task_priority' => $this->task->priority,
                'task_due_date' => $this->task->due_date?->toDateString(),
                'task_assignee' => $this->task->assignedTo?->name,
            ],
        ];
    }
}
