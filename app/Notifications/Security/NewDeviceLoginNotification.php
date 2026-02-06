<?php

declare(strict_types=1);

namespace App\Notifications\Security;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class NewDeviceLoginNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        private readonly string $ipAddress,
        private readonly string $userAgent,
        private readonly ?string $location = null,
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
            ->subject(__('notifications.security.new_device_login.subject'))
            ->greeting(__('notifications.security.new_device_login.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.security.new_device_login.line1'))
            ->line(__('notifications.security.new_device_login.line2', [
                'ipAddress' => $this->ipAddress,
                'userAgent' => $this->userAgent,
            ]));

        if ($this->location !== null) {
            $message->line(__('notifications.security.new_device_login.line3', ['location' => $this->location]));
        }

        return $message
            ->line(__('notifications.security.new_device_login.line4'))
            ->action(__('notifications.security.new_device_login.action'), route('profile.edit'))
            ->line(__('notifications.security.new_device_login.line5'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $body = __('notifications.security.new_device_login.body', [
            'ipAddress' => $this->ipAddress,
            'userAgent' => $this->userAgent,
        ]);

        if ($this->location !== null) {
            $body .= ' '.__('notifications.security.new_device_login.body_location', ['location' => $this->location]);
        }

        return [
            'title' => __('notifications.security.new_device_login.title'),
            'body' => $body,
            'action_url' => route('profile.edit'),
        ];
    }
}
