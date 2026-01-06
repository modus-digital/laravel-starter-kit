<?php

declare(strict_types=1);

namespace App\Listeners\Modules\Mailgun;

use App\Enums\Modules\Mailgun\EmailStatus;
use App\Models\Modules\Mailgun\EmailMessage;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class LogEmailAttempt
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        // Only process if module is enabled
        if (! config('modules.mailgun_analytics.enabled', false)) {
            return;
        }

        $message = $event->message;
        $original = $event->data['mailable'] ?? null;

        // Extract correlation ID from headers
        $correlationId = $this->extractCorrelationId($message);
        if (! $correlationId) {
            $correlationId = (string) Str::uuid();
        }

        // Extract Mailgun Message-ID from sent message
        $mailgunMessageId = $this->extractMailgunMessageId($message);

        // Extract recipients
        $toAddresses = $this->extractAddresses($message->getTo());
        $ccAddresses = $this->extractAddresses($message->getCc());
        $bccAddresses = $this->extractAddresses($message->getBcc());

        // Get primary recipient (first TO address)
        $primaryRecipient = $toAddresses[0] ?? null;
        if (! $primaryRecipient) {
            return; // No recipient, skip logging
        }

        // Extract from address
        $fromAddresses = $this->extractAddresses($message->getFrom());
        $fromData = $fromAddresses[0] ?? null;
        $fromAddress = $fromData['address'] ?? config('mail.from.address', 'noreply@example.com');
        $fromName = $fromData['name'] ?? config('mail.from.name', null);

        // Extract tags from headers
        $tags = $this->extractTags($message);

        // Get mailable class name
        $mailableClass = $original ? get_class($original) : null;

        // Create email message record
        $emailMessage = new EmailMessage;
        $emailMessage->correlation_id = $correlationId;
        $emailMessage->mailgun_message_id = $mailgunMessageId;
        $emailMessage->mailable_class = $mailableClass;
        $emailMessage->subject = $message->getSubject() ?? '';
        $emailMessage->from_address = $fromAddress;
        $emailMessage->from_name = $fromName;
        $emailMessage->to_address = $primaryRecipient['address'];
        $emailMessage->to_name = $primaryRecipient['name'];
        $emailMessage->cc = ! empty($ccAddresses) ? $ccAddresses : null;
        $emailMessage->bcc = ! empty($bccAddresses) ? $bccAddresses : null;
        $emailMessage->tags = ! empty($tags) ? $tags : null;
        $emailMessage->status = EmailStatus::ATTEMPTED;
        $emailMessage->sent_at = now();
        $emailMessage->save();
    }

    /**
     * Extract correlation ID from message headers.
     */
    private function extractCorrelationId(Email $message): ?string
    {
        $headers = $message->getHeaders();
        $correlationHeader = $headers->get('X-Correlation-ID');

        return $correlationHeader ? $correlationHeader->getBodyAsString() : null;
    }

    /**
     * Extract Mailgun Message-ID from message headers.
     */
    private function extractMailgunMessageId(Email $message): ?string
    {
        $headers = $message->getHeaders();
        $messageIdHeader = $headers->get('Message-ID');

        if (! $messageIdHeader) {
            return null;
        }

        $messageId = $messageIdHeader->getBodyAsString();

        // Remove angle brackets if present
        return mb_trim($messageId, '<>');
    }

    /**
     * Extract addresses from Symfony Address array.
     *
     * @param  array<int, Address>|null  $addresses
     * @return array<int, array{address: string, name: string|null}>
     */
    private function extractAddresses(?array $addresses): array
    {
        if (! $addresses) {
            return [];
        }

        return array_map(function (Address $address): array {
            return [
                'address' => $address->getAddress(),
                'name' => $address->getName(),
            ];
        }, $addresses);
    }

    /**
     * Extract name from an Address.
     */
    private function extractName(?Address $address): ?string
    {
        return $address?->getName();
    }

    /**
     * Extract tags from Mailgun headers.
     *
     * @return array<int, string>|null
     */
    private function extractTags(Email $message): ?array
    {
        $headers = $message->getHeaders();
        $tagHeader = $headers->get('X-Mailgun-Tag');

        if (! $tagHeader) {
            return null;
        }

        $tags = $tagHeader->getBodyAsString();

        // Tags can be comma-separated
        return array_map('trim', explode(',', $tags));
    }
}
