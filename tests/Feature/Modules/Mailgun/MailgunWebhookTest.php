<?php

declare(strict_types=1);

use App\Enums\Modules\Mailgun\EmailEventType;
use App\Enums\Modules\Mailgun\EmailStatus;
use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('modules.mailgun_analytics.enabled', true);
    Config::set('services.mailgun.webhook_key', 'test-webhook-key');
});

test('webhook rejects request with invalid signature', function () {
    $response = $this->postJson('/webhooks/mailgun', [
        'signature' => [
            'timestamp' => time(),
            'token' => 'test-token',
            'signature' => 'invalid-signature',
        ],
        'event-data' => [
            'event' => 'delivered',
            'id' => 'test-event-id',
        ],
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'Invalid signature']);
});

test('webhook rejects request with missing signature data', function () {
    $response = $this->postJson('/webhooks/mailgun', [
        'event-data' => [
            'event' => 'delivered',
            'id' => 'test-event-id',
        ],
    ]);

    $response->assertStatus(403);
});

test('webhook rejects request with stale timestamp', function () {
    $timestamp = Carbon::now()->subMinutes(10)->timestamp;
    $token = 'test-token';
    $data = $timestamp.$token;
    $signature = hash_hmac('sha256', $data, 'test-webhook-key');

    $response = $this->postJson('/webhooks/mailgun', [
        'signature' => [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
        ],
        'event-data' => [
            'event' => 'delivered',
            'id' => 'test-event-id',
        ],
    ]);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'Timestamp too old']);
});

test('webhook processes delivered event and updates email status', function () {
    $emailMessage = EmailMessage::factory()->create([
        'mailgun_message_id' => '<test-message-id@mailgun.com>',
        'status' => EmailStatus::ATTEMPTED,
    ]);

    $timestamp = time();
    $token = 'test-token';
    $data = $timestamp.$token;
    $signature = hash_hmac('sha256', $data, 'test-webhook-key');

    $response = $this->postJson('/webhooks/mailgun', [
        'signature' => [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
        ],
        'event-data' => [
            'event' => 'delivered',
            'id' => 'test-event-id-1',
            'timestamp' => $timestamp,
            'recipient' => $emailMessage->to_address,
            'message' => [
                'headers' => [
                    'message-id' => '<test-message-id@mailgun.com>',
                ],
            ],
        ],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('email_events', [
        'email_message_id' => $emailMessage->id,
        'event_type' => EmailEventType::DELIVERED->value,
        'mailgun_event_id' => 'test-event-id-1',
    ]);

    $emailMessage->refresh();
    expect($emailMessage->status)->toBe(EmailStatus::DELIVERED);
});

test('webhook prevents duplicate events using mailgun event id', function () {
    $emailMessage = EmailMessage::factory()->create([
        'mailgun_message_id' => '<test-message-id@mailgun.com>',
    ]);

    EmailEvent::factory()->create([
        'email_message_id' => $emailMessage->id,
        'mailgun_event_id' => 'test-event-id-1',
    ]);

    $timestamp = time();
    $token = 'test-token';
    $data = $timestamp.$token;
    $signature = hash_hmac('sha256', $data, 'test-webhook-key');

    $response = $this->postJson('/webhooks/mailgun', [
        'signature' => [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
        ],
        'event-data' => [
            'event' => 'delivered',
            'id' => 'test-event-id-1', // Same ID
            'timestamp' => $timestamp,
            'recipient' => $emailMessage->to_address,
            'message' => [
                'headers' => [
                    'message-id' => '<test-message-id@mailgun.com>',
                ],
            ],
        ],
    ]);

    $response->assertStatus(200);

    // Should not create duplicate event
    expect(EmailEvent::where('mailgun_event_id', 'test-event-id-1')->count())->toBe(1);
});

test('webhook finds email by correlation id when message id not found', function () {
    $emailMessage = EmailMessage::factory()->create([
        'correlation_id' => 'test-correlation-id',
        'mailgun_message_id' => null,
    ]);

    $timestamp = time();
    $token = 'test-token';
    $data = $timestamp.$token;
    $signature = hash_hmac('sha256', $data, 'test-webhook-key');

    $response = $this->postJson('/webhooks/mailgun', [
        'signature' => [
            'timestamp' => $timestamp,
            'token' => $token,
            'signature' => $signature,
        ],
        'event-data' => [
            'event' => 'accepted',
            'id' => 'test-event-id-2',
            'timestamp' => $timestamp,
            'recipient' => $emailMessage->to_address,
            'message' => [
                'headers' => [
                    'x-correlation-id' => 'test-correlation-id',
                ],
            ],
        ],
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('email_events', [
        'email_message_id' => $emailMessage->id,
        'mailgun_event_id' => 'test-event-id-2',
    ]);
});

test('webhook returns 403 when module is disabled', function () {
    Config::set('modules.mailgun_analytics.enabled', false);

    $response = $this->postJson('/webhooks/mailgun', []);

    $response->assertStatus(403);
    $response->assertJson(['message' => 'Module disabled']);
});
