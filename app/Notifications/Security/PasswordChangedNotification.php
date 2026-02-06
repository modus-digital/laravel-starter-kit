<?php

declare(strict_types=1);

namespace App\Notifications\Security;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordChangedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        private readonly ?string $ipAddress = null,
        private readonly ?string $userAgent = null,
        private readonly array $channels = [],
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
        $message = (new MailMessage)
            ->subject(__('notifications.security.password_changed.subject'))
            ->greeting(__('notifications.security.password_changed.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.security.password_changed.line1'));

        if ($this->ipAddress !== null) {
            $message->line(__('notifications.security.password_changed.line2', [
                'ipAddress' => $this->ipAddress,
                'userAgent' => $this->userAgent ?? __('notifications.security.password_changed.unknown_device'),
            ]));
        }

        return $message
            ->line(__('notifications.security.password_changed.line3'))
            ->line(__('notifications.security.password_changed.line4'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $body = __('notifications.security.password_changed.body');

        if ($this->ipAddress !== null) {
            $body .= ' '.__('notifications.security.password_changed.body_details', [
                'ipAddress' => $this->ipAddress,
                'userAgent' => $this->userAgent ?? __('notifications.security.password_changed.unknown_device'),
            ]);
        }

        return [
            'title' => __('notifications.security.password_changed.title'),
            'body' => $body,
            'action_url' => route('profile.edit'),
        ];
    }
}
