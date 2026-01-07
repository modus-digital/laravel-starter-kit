<?php

declare(strict_types=1);

namespace App\Notifications\Tasks;

use App\Models\Modules\Tasks\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

final class TaskDueReminderNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        private readonly Task $task,
        private readonly int $daysUntilDue,
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
        /** @var \App\Models\User $notifiable */
        $dueDate = $this->task->due_date?->format('Y-m-d') ?? '';

        return (new MailMessage)
            ->subject(__('notifications.tasks.due_reminder.subject', ['title' => $this->task->title]))
            ->greeting(__('notifications.tasks.due_reminder.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.tasks.due_reminder.line1', [
                'title' => $this->task->title,
                'days' => $this->daysUntilDue,
                'dueDate' => $dueDate,
            ]))
            ->action(__('notifications.tasks.due_reminder.action'), route('tasks.show', $this->task))
            ->line(__('notifications.tasks.due_reminder.line2'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $dueDate = $this->task->due_date?->format('Y-m-d') ?? '';
        $titleKey = 'notifications.tasks.due_reminder.title';
        $bodyKey = 'notifications.tasks.due_reminder.body';
        $replacements = [
            'title' => $this->task->title,
            'days' => $this->daysUntilDue,
            'dueDate' => $dueDate,
        ];

        return [
            'title' => __($titleKey, [
                'title' => $this->task->title,
                'days' => $this->daysUntilDue,
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
