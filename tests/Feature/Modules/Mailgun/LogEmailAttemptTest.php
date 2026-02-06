<?php

declare(strict_types=1);

use App\Enums\Modules\Mailgun\EmailStatus;
use App\Listeners\Modules\Mailgun\LogEmailAttempt;
use App\Models\Modules\Mailgun\EmailMessage;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Mime\Email as SymfonyEmail;

beforeEach(function () {
    Config::set('modules.mailgun_analytics.enabled', true);
    Config::set('mail.from.address', 'noreply@example.com');
    Config::set('mail.from.name', 'Test App');
});

test('listener creates email message record when email is sent', function () {
    $mailable = new class extends Illuminate\Mail\Mailable
    {
        public function build()
        {
            return $this->subject('Test Email')
                ->to('recipient@example.com')
                ->view('emails.test');
        }
    };

    $message = new SymfonyEmail();
    $message->subject('Test Email');
    $message->to('recipient@example.com');
    $message->from('noreply@example.com', 'Test App');

    $event = new MessageSent(
        sent: new Illuminate\Mail\SentMessage($message, $message),
        data: ['mailable' => $mailable],
    );

    $listener = app(LogEmailAttempt::class);
    $listener->handle($event);

    $this->assertDatabaseHas('email_messages', [
        'subject' => 'Test Email',
        'to_address' => 'recipient@example.com',
        'from_address' => 'noreply@example.com',
        'status' => EmailStatus::ATTEMPTED->value,
    ]);

    $emailMessage = EmailMessage::where('to_address', 'recipient@example.com')->first();
    expect($emailMessage)->not->toBeNull()
        ->and($emailMessage->subject)->toBe('Test Email')
        ->and($emailMessage->status)->toBe(EmailStatus::ATTEMPTED)
        ->and($emailMessage->sent_at)->not->toBeNull();
});

test('listener extracts correlation id from headers', function () {
    $mailable = new class extends Illuminate\Mail\Mailable
    {
        public function build()
        {
            return $this->subject('Test Email')
                ->to('recipient@example.com')
                ->view('emails.test');
        }
    };

    $message = new SymfonyEmail();
    $message->subject('Test Email');
    $message->to('recipient@example.com');
    $message->from('noreply@example.com');
    $message->getHeaders()->addTextHeader('X-Correlation-ID', 'test-correlation-id');

    $event = new MessageSent(
        sent: new Illuminate\Mail\SentMessage($message, $message),
        data: ['mailable' => $mailable],
    );

    $listener = app(LogEmailAttempt::class);
    $listener->handle($event);

    $emailMessage = EmailMessage::where('to_address', 'recipient@example.com')->first();
    expect($emailMessage->correlation_id)->toBe('test-correlation-id');
});

test('listener extracts mailgun message id from headers', function () {
    $mailable = new class extends Illuminate\Mail\Mailable
    {
        public function build()
        {
            return $this->subject('Test Email')
                ->to('recipient@example.com')
                ->view('emails.test');
        }
    };

    $message = new SymfonyEmail();
    $message->subject('Test Email');
    $message->to('recipient@example.com');
    $message->from('noreply@example.com');
    $message->getHeaders()->addIdHeader('Message-ID', '<test-message-id@mailgun.com>');

    $event = new MessageSent(
        sent: new Illuminate\Mail\SentMessage($message, $message),
        data: ['mailable' => $mailable],
    );

    $listener = app(LogEmailAttempt::class);
    $listener->handle($event);

    $emailMessage = EmailMessage::where('to_address', 'recipient@example.com')->first();
    expect($emailMessage->mailgun_message_id)->toBe('test-message-id@mailgun.com');
});

test('listener extracts tags from mailgun headers', function () {
    $mailable = new class extends Illuminate\Mail\Mailable
    {
        public function build()
        {
            return $this->subject('Test Email')
                ->to('recipient@example.com')
                ->view('emails.test');
        }
    };

    $message = new SymfonyEmail();
    $message->subject('Test Email');
    $message->to('recipient@example.com');
    $message->from('noreply@example.com');
    $message->getHeaders()->addTextHeader('X-Mailgun-Tag', 'welcome,notification');

    $event = new MessageSent(
        sent: new Illuminate\Mail\SentMessage($message, $message),
        data: ['mailable' => $mailable],
    );

    $listener = app(LogEmailAttempt::class);
    $listener->handle($event);

    $emailMessage = EmailMessage::where('to_address', 'recipient@example.com')->first();
    expect($emailMessage->tags)->toBe(['welcome', 'notification']);
});

test('listener does not create record when module is disabled', function () {
    Config::set('modules.mailgun_analytics.enabled', false);

    $mailable = new class extends Illuminate\Mail\Mailable
    {
        public function build()
        {
            return $this->subject('Test Email')
                ->to('recipient@example.com')
                ->view('emails.test');
        }
    };

    $message = new SymfonyEmail();
    $message->subject('Test Email');
    $message->to('recipient@example.com');

    $event = new MessageSent(
        sent: new Illuminate\Mail\SentMessage($message, $message),
        data: ['mailable' => $mailable],
    );

    $listener = app(LogEmailAttempt::class);
    $listener->handle($event);

    expect(EmailMessage::count())->toBe(0);
});
