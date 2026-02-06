<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Auth\AccountCreated;
use Illuminate\Console\Command;

final class SendTestEmailCommand extends Command
{
    protected $signature = 'mail:test {email?}';

    protected $description = 'Send a test email';

    public function handle(): int
    {
        $email = $this->argument('email') ?? 'test@example.com';

        $user = User::where('email', $email)->first() ?? User::first();

        if (! $user) {
            $this->error('No user found');

            return self::FAILURE;
        }

        $this->info("Sending test email to: {$user->email}");

        $user->notify(new AccountCreated('test-password-123'));

        $this->info('✅ Email sent successfully!');

        return self::SUCCESS;
    }
}
