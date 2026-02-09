<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Enums\Modules\Mailgun\EmailEventType;
use App\Enums\Modules\Mailgun\EmailStatus;
use App\Http\Controllers\Controller;
use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Log;

final class MailgunWebhookController extends Controller
{
    /**
     * Handle Mailgun webhook events.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Only process if module is enabled
        if (! config('modules.mailgun_analytics.enabled', false)) {
            return response()->json(['message' => 'Module disabled'], 403);
        }

        // Verify signature
        if (! $this->verifySignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Check timestamp freshness (replay protection)
        if (! $this->isTimestampFresh($request)) {
            return response()->json(['message' => 'Timestamp too old'], 403);
        }

        // Process event
        try {
            $this->processEvent($request);

            return response()->json(['message' => 'Event processed'], 200);
        } catch (Exception $e) {
            // Log error but return 200 to prevent Mailgun retries
            Log::error('Mailgun webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['message' => 'Event processing failed'], 500);
        }
    }

    /**
     * Verify Mailgun webhook signature.
     */
    private function verifySignature(Request $request): bool
    {
        $signature = $request->input('signature');
        $timestamp = $signature['timestamp'] ?? null;
        $token = $signature['token'] ?? null;
        $providedSignature = $signature['signature'] ?? null;

        if (! $timestamp || ! $token || ! $providedSignature) {
            return false;
        }

        // Try to get webhook key from database settings (encrypted), fallback to config/env
        $webhookKey = $this->getWebhookKey();
        if (! $webhookKey) {
            return false;
        }

        // Create signature data
        $data = $timestamp.$token;
        $expectedSignature = hash_hmac('sha256', $data, $webhookKey);

        return hash_equals($expectedSignature, $providedSignature);
    }

    /**
     * Get the Mailgun webhook signing key from settings or config.
     */
    private function getWebhookKey(): ?string
    {
        // First try to get from database settings (stored encrypted)
        $encryptedKey = \Outerweb\Settings\Facades\Setting::get('integrations.mailgun.webhook_signing_key');

        if ($encryptedKey) {
            try {
                return decrypt($encryptedKey);
            } catch (Exception) {
                // If decryption fails, might be plain text (legacy)
                return $encryptedKey;
            }
        }

        // Fallback to environment variable
        return config('services.mailgun.webhook_key');
    }

    /**
     * Check if timestamp is fresh (within 5 minutes).
     */
    private function isTimestampFresh(Request $request): bool
    {
        $signature = $request->input('signature');
        $timestamp = $signature['timestamp'] ?? null;

        if (! $timestamp) {
            return false;
        }

        $eventTime = Carbon::createFromTimestamp((int) $timestamp);
        $now = Carbon::now();

        // Allow events within 5 minutes (check absolute difference)
        return abs($now->diffInMinutes($eventTime, false)) <= 5;
    }

    /**
     * Process Mailgun event.
     */
    private function processEvent(Request $request): void
    {
        $eventData = $request->input('event-data', []);
        $eventType = $eventData['event'] ?? null;
        $mailgunEventId = $eventData['id'] ?? null;

        Log::info('Mailgun webhook received', [
            'event_type' => $eventType,
            'event_id' => $mailgunEventId,
        ]);

        if (! $eventType || ! $mailgunEventId) {
            Log::warning('Mailgun webhook missing event type or ID');

            return;
        }

        // Check for duplicate event (idempotency)
        if (EmailEvent::where('mailgun_event_id', $mailgunEventId)->exists()) {
            Log::info('Mailgun webhook duplicate event', ['event_id' => $mailgunEventId]);

            return;
        }

        // Map Mailgun event type to our enum
        $emailEventType = $this->mapEventType($eventType);
        if (! $emailEventType instanceof EmailEventType) {
            Log::warning('Mailgun webhook unknown event type', ['event_type' => $eventType]);

            return; // Unknown event type, skip
        }

        // Find email message by Mailgun Message-ID or correlation ID
        $emailMessage = $this->findEmailMessage($eventData);

        if (! $emailMessage instanceof EmailMessage) {
            // Event for unknown email - could be from before module was enabled
            // We could optionally create a stub record here, but for now we'll skip
            Log::warning('Mailgun webhook email message not found', [
                'message_id' => $eventData['message']['headers']['message-id'] ?? null,
                'correlation_id' => $eventData['message']['headers']['x-correlation-id'] ?? null,
            ]);

            return;
        }

        // Create event record
        EmailEvent::create([
            'email_message_id' => $emailMessage->id,
            'event_type' => $emailEventType,
            'mailgun_event_id' => $mailgunEventId,
            'severity' => $eventData['severity'] ?? null,
            'reason' => $eventData['reason'] ?? null,
            'recipient' => $eventData['recipient'] ?? $emailMessage->to_address,
            'ip_address' => $eventData['client-info']['client-ip'] ?? null,
            'user_agent' => $eventData['client-info']['client-type'] ?? null,
            'url' => $eventData['url'] ?? null,
            'raw_payload' => $eventData,
            'occurred_at' => Carbon::createFromTimestamp($eventData['timestamp'] ?? time()),
        ]);

        // Update email message status based on event priority
        $this->updateEmailStatus($emailMessage, $emailEventType);

        Log::info('Mailgun webhook event processed successfully', [
            'event_id' => $mailgunEventId,
            'email_message_id' => $emailMessage->id,
            'event_type' => $emailEventType->value,
        ]);
    }

    /**
     * Map Mailgun event type to EmailEventType enum.
     */
    private function mapEventType(string $mailgunEventType): ?EmailEventType
    {
        return match (mb_strtolower($mailgunEventType)) {
            'accepted' => EmailEventType::ACCEPTED,
            'delivered' => EmailEventType::DELIVERED,
            'failed' => EmailEventType::FAILED,
            'rejected' => EmailEventType::REJECTED,
            'bounced' => EmailEventType::FAILED, // Mailgun bounced events map to FAILED
            'opened' => EmailEventType::OPENED,
            'clicked' => EmailEventType::CLICKED,
            'unsubscribed' => EmailEventType::UNSUBSCRIBED,
            'complained' => EmailEventType::COMPLAINED,
            'stored' => EmailEventType::STORED,
            default => null,
        };
    }

    /**
     * Find email message by Mailgun Message-ID, correlation ID, or recipient matching.
     */
    private function findEmailMessage(array $eventData): ?EmailMessage
    {
        $messageId = $eventData['message']['headers']['message-id'] ?? null;
        // Remove angle brackets if present (case-insensitive for both forms)
        if ($messageId && (str_starts_with((string) $messageId, '<') && str_ends_with((string) $messageId, '>'))) {
            $messageId = mb_substr((string) $messageId, 1, mb_strlen((string) $messageId) - 2);
        }

        // Try to find by Mailgun Message-ID first
        if ($messageId) {
            $emailMessage = EmailMessage::where('mailgun_message_id', $messageId)->first();
            if (! $emailMessage) {
                // Also try with angle brackets added
                $emailMessage = EmailMessage::where('mailgun_message_id', '<'.$messageId.'>')->first();
            }
            if ($emailMessage) {
                return $emailMessage;
            }
        }

        // Try to find by correlation ID from headers
        $headers = $eventData['message']['headers'] ?? [];
        $correlationId = $headers['x-correlation-id'] ?? null;
        if ($correlationId) {
            $emailMessage = EmailMessage::where('correlation_id', $correlationId)->first();

            // If found by correlation ID, update it with the Mailgun Message-ID for future lookups
            if ($emailMessage && $messageId && ! $emailMessage->mailgun_message_id) {
                $emailMessage->mailgun_message_id = $messageId;
                $emailMessage->save();

                Log::info('Updated EmailMessage with Mailgun Message-ID', [
                    'email_message_id' => $emailMessage->id,
                    'mailgun_message_id' => $messageId,
                    'correlation_id' => $correlationId,
                ]);
            }

            return $emailMessage;
        }

        // Fallback: Try to find by recipient + subject + recent timestamp (last 5 minutes)
        $recipient = $eventData['recipient'] ?? null;
        $subject = $eventData['message']['headers']['subject'] ?? null;
        $timestamp = $eventData['timestamp'] ?? null;

        if ($recipient && $subject && $timestamp) {
            $eventTime = Carbon::createFromTimestamp($timestamp);
            $windowStart = $eventTime->copy()->subMinutes(5);
            $windowEnd = $eventTime->copy()->addMinutes(1);

            $emailMessage = EmailMessage::where('to_address', $recipient)
                ->where('subject', $subject)
                ->whereBetween('sent_at', [$windowStart, $windowEnd])
                ->orderBy('sent_at', 'desc')
                ->first();

            // If found, update it with the Mailgun Message-ID for future lookups
            if ($emailMessage && $messageId && ! $emailMessage->mailgun_message_id) {
                $emailMessage->mailgun_message_id = $messageId;
                $emailMessage->save();

                Log::info('Updated EmailMessage with Mailgun Message-ID via recipient matching', [
                    'email_message_id' => $emailMessage->id,
                    'mailgun_message_id' => $messageId,
                    'recipient' => $recipient,
                    'subject' => $subject,
                ]);
            }

            return $emailMessage;
        }

        return null;
    }

    /**
     * Update email message status based on event priority.
     */
    private function updateEmailStatus(EmailMessage $emailMessage, EmailEventType $eventType): void
    {
        // Handle bounced events specially - they should map to BOUNCED status
        $eventData = request()->input('event-data', []);
        $mailgunEventType = mb_strtolower($eventData['event'] ?? '');

        $newStatus = match ($mailgunEventType) {
            'bounced' => EmailStatus::BOUNCED,
            default => $eventType->toEmailStatus(),
        };

        // Only update if new status has higher priority
        $currentPriority = $emailMessage->status->getPriority();
        $newPriority = $newStatus->getPriority();

        if ($newPriority > $currentPriority) {
            $emailMessage->update(['status' => $newStatus]);
        }
    }
}
