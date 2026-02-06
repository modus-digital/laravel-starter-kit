<?php

declare(strict_types=1);

namespace App\Notifications\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

final class CommentAddedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     * @param  array<string, mixed>  $comment
     */
    public function __construct(
        private readonly Task $task,
        private readonly User $commenter,
        private readonly array $comment,
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
            ->subject(__('notifications.comments.added.subject', ['title' => $this->task->title]))
            ->greeting(__('notifications.comments.added.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.comments.added.line1', [
                'commenter' => $this->commenter->name,
                'title' => $this->task->title,
            ]))
            ->action(__('notifications.comments.added.action'), route('tasks.show', $this->task))
            ->line(__('notifications.comments.added.line2'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $titleKey = 'notifications.comments.added.title';
        $bodyKey = 'notifications.comments.added.body';
        $replacements = [
            'commenter' => $this->commenter->name,
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
                'type' => 'comment',
                'comment_preview' => Str::limit($this->extractTextFromTiptapJson($this->comment), 200),
                'task_title' => $this->task->title,
            ],
        ];
    }

    /**
     * Extract plain text from TipTap JSON content.
     *
     * @param  array<string, mixed>  $json
     */
    private function extractTextFromTiptapJson(array $json): string
    {
        $text = '';

        if (isset($json['content']) && is_array($json['content'])) {
            foreach ($json['content'] as $node) {
                $text .= $this->extractTextFromNode($node);
            }
        }

        return mb_trim($text);
    }

    /**
     * Recursively extract text from a TipTap node.
     *
     * @param  array<string, mixed>  $node
     */
    private function extractTextFromNode(array $node): string
    {
        $text = '';

        // If node has text property, add it
        if (isset($node['text']) && is_string($node['text'])) {
            $text .= $node['text'];
        }

        // If node has content (nested nodes), recursively extract
        if (isset($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $childNode) {
                $text .= $this->extractTextFromNode($childNode);
            }
        }

        // Add spacing between block-level nodes
        if (isset($node['type']) && in_array($node['type'], ['paragraph', 'heading', 'listItem'])) {
            $text .= ' ';
        }

        return $text;
    }
}
