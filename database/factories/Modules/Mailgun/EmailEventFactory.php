<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Mailgun;

use App\Enums\Modules\Mailgun\EmailEventType;
use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Mailgun\EmailEvent>
 */
final class EmailEventFactory extends Factory
{
    protected $model = EmailEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_message_id' => EmailMessage::factory(),
            'event_type' => EmailEventType::DELIVERED,
            'mailgun_event_id' => Str::random(32),
            'severity' => null,
            'reason' => null,
            'recipient' => fake()->safeEmail(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'url' => null,
            'raw_payload' => [
                'event' => 'delivered',
                'timestamp' => now()->timestamp,
                'recipient' => fake()->safeEmail(),
            ],
            'occurred_at' => now(),
        ];
    }
}
