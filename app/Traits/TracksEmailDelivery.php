<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Str;

trait TracksEmailDelivery
{
    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Correlation-ID' => $this->getCorrelationId(),
                'X-Mailable-Class' => static::class,
            ],
        );
    }

    /**
     * Get or generate a correlation ID for this email.
     */
    protected function getCorrelationId(): string
    {
        // If the mailable has a correlation_id property, use it
        if (property_exists($this, 'correlationId') && $this->correlationId !== null) {
            return (string) $this->correlationId;
        }

        // Otherwise, generate a new UUID
        return (string) Str::uuid();
    }
}
