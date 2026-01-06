<?php

declare(strict_types=1);

namespace App\Console\Commands\Modules\Mailgun;

use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use Illuminate\Console\Command;

final class DebugMailgunCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailgun:debug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug Mailgun analytics module status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Mailgun Analytics Debug Information');
        $this->newLine();

        // Check if module is enabled
        $enabled = config('modules.mailgun_analytics.enabled', false);
        $this->line('Module Enabled: '.($enabled ? '✅ Yes' : '❌ No'));

        if (! $enabled) {
            $this->warn('Set MAILGUN_ANALYTICS_ENABLED=true in your .env file');
        }

        $this->newLine();

        // Check webhook key
        $webhookKey = config('services.mailgun.webhook_key');
        $this->line('Webhook Key Configured: '.($webhookKey ? '✅ Yes' : '❌ No'));

        if (! $webhookKey) {
            $this->warn('Set MAILGUN_WEBHOOK_SIGNING_KEY in your .env file');
        }

        $this->newLine();

        // Check database tables
        try {
            $messageCount = EmailMessage::count();
            $eventCount = EmailEvent::count();

            $this->line("Email Messages: {$messageCount}");
            $this->line("Email Events: {$eventCount}");

            if ($messageCount > 0) {
                $this->newLine();
                $this->info('Recent Email Messages:');
                $messages = EmailMessage::latest('sent_at')->take(5)->get();

                foreach ($messages as $message) {
                    $this->line("  - {$message->subject} → {$message->to_address} [{$message->status->value}]");
                    $this->line("    Mailgun ID: ".($message->mailgun_message_id ?? 'Not set'));
                    $this->line("    Correlation ID: {$message->correlation_id}");
                    $this->line("    Events: {$message->events()->count()}");
                }
            }
        } catch (\Exception $e) {
            $this->error('Database Error: '.$e->getMessage());
            $this->warn('Run: php artisan migrate');
        }

        $this->newLine();

        // Check recent logs
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $this->info('Recent Mailgun Webhook Logs:');
            $logs = shell_exec("tail -n 50 {$logFile} | grep -i mailgun");
            if ($logs) {
                $this->line($logs);
            } else {
                $this->warn('No Mailgun webhook logs found');
            }
        }

        return self::SUCCESS;
    }
}
