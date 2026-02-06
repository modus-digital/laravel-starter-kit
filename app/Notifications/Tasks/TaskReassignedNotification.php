<?php

declare(strict_types=1);

namespace App\Notifications\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

final class TaskReassignedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        private readonly Task $task,
        private readonly ?User $previousAssignee,
        private readonly ?User $newAssignee,
        private readonly User $reassigner,
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
        $message = (new MailMessage)
            ->subject(__('notifications.tasks.reassigned.subject', ['title' => $this->task->title]));

        if ($notifiable->id === $this->newAssignee?->id) {
            // Notifying the new assignee
            $message->greeting(__('notifications.tasks.reassigned.greeting_assigned', ['name' => $notifiable->name]))
                ->line(__('notifications.tasks.reassigned.line1_assigned', [
                    'reassigner' => $this->reassigner->name,
                    'title' => $this->task->title,
                ]));
        } else {
            // Notifying the previous assignee
            $message->greeting(__('notifications.tasks.reassigned.greeting_unassigned', ['name' => $notifiable->name]))
                ->line(__('notifications.tasks.reassigned.line1_unassigned', [
                    'reassigner' => $this->reassigner->name,
                    'title' => $this->task->title,
                    'newAssignee' => $this->newAssignee?->name ?? __('notifications.tasks.reassigned.unassigned'),
                ]));
        }

        return $message
            ->action(__('notifications.tasks.reassigned.action'), route('tasks.show', $this->task))
            ->line(__('notifications.tasks.reassigned.line2'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        if ($notifiable->id === $this->newAssignee?->id) {
            $titleKey = 'notifications.tasks.reassigned.title_assigned';
            $bodyKey = 'notifications.tasks.reassigned.body_assigned';
            $replacements = [
                'reassigner' => $this->reassigner->name,
                'title' => $this->task->title,
            ];
        } else {
            $titleKey = 'notifications.tasks.reassigned.title_unassigned';
            $bodyKey = 'notifications.tasks.reassigned.body_unassigned';
            $replacements = [
                'reassigner' => $this->reassigner->name,
                'title' => $this->task->title,
                'newAssignee' => $this->newAssignee?->name ?? __('notifications.tasks.reassigned.unassigned'),
            ];
        }

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
