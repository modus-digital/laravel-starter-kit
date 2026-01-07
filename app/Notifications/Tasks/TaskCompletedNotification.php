<?php

declare(strict_types=1);

namespace App\Notifications\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

final class TaskCompletedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        private readonly Task $task,
        private readonly User $completedBy,
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
        return (new MailMessage)
            ->subject(__('notifications.tasks.completed.subject', ['title' => $this->task->title]))
            ->greeting(__('notifications.tasks.completed.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.tasks.completed.line1', [
                'completedBy' => $this->completedBy->name,
                'title' => $this->task->title,
            ]))
            ->action(__('notifications.tasks.completed.action'), route('tasks.show', $this->task))
            ->line(__('notifications.tasks.completed.line2'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $titleKey = 'notifications.tasks.completed.title';
        $bodyKey = 'notifications.tasks.completed.body';
        $replacements = [
            'completedBy' => $this->completedBy->name,
            'title' => $this->task->title,
        ];

        return [
            'title' => __($titleKey, $replacements),
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
