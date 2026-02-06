<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Mailgun;

use App\Enums\Modules\Mailgun\EmailStatus;
use App\Models\Modules\Mailgun\EmailMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Mailgun\EmailMessage>
 */
final class EmailMessageFactory extends Factory
{
    protected $model = EmailMessage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mailable_class' => 'App\Mail\TestEmail',
            'subject' => fake()->sentence(),
            'from_address' => fake()->safeEmail(),
            'from_name' => fake()->name(),
            'to_address' => fake()->safeEmail(),
            'to_name' => fake()->name(),
            'cc' => null,
            'bcc' => null,
            'tags' => null,
            'mailgun_message_id' => '<'.Str::random(32).'@mailgun.com>',
            'correlation_id' => (string) Str::uuid(),
            'status' => EmailStatus::ATTEMPTED,
            'sent_at' => now(),
        ];
    }
}
