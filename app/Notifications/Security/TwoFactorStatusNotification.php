<?php

declare(strict_types=1);

namespace App\Notifications\Security;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TwoFactorStatusNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $channels
     */
    public function __construct(
        private readonly bool $enabled,
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
            ->subject($this->enabled
                ? __('notifications.security.two_factor_enabled.subject')
                : __('notifications.security.two_factor_disabled.subject'))
            ->greeting($this->enabled
                ? __('notifications.security.two_factor_enabled.greeting', ['name' => $notifiable->name])
                : __('notifications.security.two_factor_disabled.greeting', ['name' => $notifiable->name]))
            ->line($this->enabled
                ? __('notifications.security.two_factor_enabled.line1')
                : __('notifications.security.two_factor_disabled.line1'));

        if ($this->ipAddress !== null) {
            $message->line($this->enabled
                ? __('notifications.security.two_factor_enabled.line2', [
                    'ipAddress' => $this->ipAddress,
                    'userAgent' => $this->userAgent ?? __('notifications.security.two_factor_enabled.unknown_device'),
                ])
                : __('notifications.security.two_factor_disabled.line2', [
                    'ipAddress' => $this->ipAddress,
                    'userAgent' => $this->userAgent ?? __('notifications.security.two_factor_disabled.unknown_device'),
                ]));
        }

        return $message
            ->line($this->enabled
                ? __('notifications.security.two_factor_enabled.line3')
                : __('notifications.security.two_factor_disabled.line3'))
            ->action(__('notifications.security.two_factor_enabled.action'), route('two-factor.show'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $body = $this->enabled
            ? __('notifications.security.two_factor_enabled.body')
            : __('notifications.security.two_factor_disabled.body');

        if ($this->ipAddress !== null) {
            $body .= ' '.($this->enabled
                ? __('notifications.security.two_factor_enabled.body_details', [
                    'ipAddress' => $this->ipAddress,
                    'userAgent' => $this->userAgent ?? __('notifications.security.two_factor_enabled.unknown_device'),
                ])
                : __('notifications.security.two_factor_disabled.body_details', [
                    'ipAddress' => $this->ipAddress,
                    'userAgent' => $this->userAgent ?? __('notifications.security.two_factor_disabled.unknown_device'),
                ]));
        }

        return [
            'title' => $this->enabled
                ? __('notifications.security.two_factor_enabled.title')
                : __('notifications.security.two_factor_disabled.title'),
            'body' => $body,
            'action_url' => route('two-factor.show'),
        ];
    }
}
