<?php

declare(strict_types=1);

namespace App\Enums\Modules\Mailgun;

enum EmailEventType: string
{
    case ACCEPTED = 'accepted';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case REJECTED = 'rejected';
    case OPENED = 'opened';
    case CLICKED = 'clicked';
    case UNSUBSCRIBED = 'unsubscribed';
    case COMPLAINED = 'complained';
    case STORED = 'stored';

    public function getColor(): string
    {
        return match ($this) {
            self::DELIVERED, self::ACCEPTED, self::STORED => 'success',
            self::OPENED, self::CLICKED => 'info',
            self::REJECTED, self::FAILED => 'danger',
            self::COMPLAINED, self::UNSUBSCRIBED => 'warning',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ACCEPTED => __('enums.mailgun.email_event_type.accepted'),
            self::DELIVERED => __('enums.mailgun.email_event_type.delivered'),
            self::FAILED => __('enums.mailgun.email_event_type.failed'),
            self::REJECTED => __('enums.mailgun.email_event_type.rejected'),
            self::OPENED => __('enums.mailgun.email_event_type.opened'),
            self::CLICKED => __('enums.mailgun.email_event_type.clicked'),
            self::UNSUBSCRIBED => __('enums.mailgun.email_event_type.unsubscribed'),
            self::COMPLAINED => __('enums.mailgun.email_event_type.complained'),
            self::STORED => __('enums.mailgun.email_event_type.stored'),
        };
    }

    /**
     * Map Mailgun event type to EmailStatus.
     */
    public function toEmailStatus(): EmailStatus
    {
        return match ($this) {
            self::ACCEPTED => EmailStatus::ACCEPTED,
            self::DELIVERED => EmailStatus::DELIVERED,
            self::FAILED => EmailStatus::FAILED,
            self::REJECTED => EmailStatus::DROPPED,
            self::COMPLAINED => EmailStatus::COMPLAINED,
            default => EmailStatus::ATTEMPTED,
        };
    }
}
