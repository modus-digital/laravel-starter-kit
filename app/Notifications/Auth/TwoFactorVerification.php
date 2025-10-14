<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class TwoFactorVerification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly string $code,
        public readonly int $expiresInMinutes = 10
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  object{name: string}  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('auth.two_factor.email.subject'))
            ->greeting(__('auth.two_factor.email.greeting', ['name' => $notifiable->name]))
            ->line(__('auth.two_factor.email.line1'))
            ->line('')
            ->line('**'.$this->code.'**')
            ->line('')
            ->line(__('auth.two_factor.email.line2', ['minutes' => $this->expiresInMinutes]))
            ->line(__('auth.two_factor.email.line3'))
            ->salutation(__('auth.two_factor.email.salutation'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code' => $this->code,
            'expires_in_minutes' => $this->expiresInMinutes,
        ];
    }
}
