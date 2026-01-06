<?php

declare(strict_types=1);

namespace App\Enums\Modules\Mailgun;

enum EmailStatus: string
{
    case ATTEMPTED = 'attempted';
    case ACCEPTED = 'accepted';
    case DELIVERED = 'delivered';
    case DROPPED = 'dropped';
    case BOUNCED = 'bounced';
    case FAILED = 'failed';
    case COMPLAINED = 'complained';

    public function getColor(): string
    {
        return match ($this) {
            self::DELIVERED => 'success',
            self::ACCEPTED => 'info',
            self::ATTEMPTED => 'gray',
            self::BOUNCED => 'warning',
            self::FAILED, self::DROPPED => 'danger',
            self::COMPLAINED => 'warning',
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::ATTEMPTED => __('enums.mailgun.email_status.attempted'),
            self::ACCEPTED => __('enums.mailgun.email_status.accepted'),
            self::DELIVERED => __('enums.mailgun.email_status.delivered'),
            self::DROPPED => __('enums.mailgun.email_status.dropped'),
            self::BOUNCED => __('enums.mailgun.email_status.bounced'),
            self::FAILED => __('enums.mailgun.email_status.failed'),
            self::COMPLAINED => __('enums.mailgun.email_status.complained'),
        };
    }

    /**
     * Get the priority for status updates (higher = more important).
     * Used to determine which status should be shown when multiple events exist.
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::DELIVERED => 100,
            self::COMPLAINED => 90,
            self::BOUNCED => 80,
            self::FAILED => 70,
            self::DROPPED => 60,
            self::ACCEPTED => 50,
            self::ATTEMPTED => 10,
        };
    }
}
