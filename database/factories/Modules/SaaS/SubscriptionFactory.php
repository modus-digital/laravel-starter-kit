<?php

declare(strict_types=1);

namespace Database\Factories\Modules\SaaS;

use App\Enums\ActivityStatus;
use App\Models\Modules\Clients\Client;
use App\Models\Modules\SaaS\Plan;
use App\Models\Modules\SaaS\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Subscription>
 */
final class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'client_id' => Client::factory(),
            'plan_id' => Plan::factory(),
            'status' => ActivityStatus::ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $this->faker->optional()->dateTimeBetween($startsAt, '+1 year'),
            'trial_ends_at' => $this->faker->optional()->dateTimeBetween($startsAt, '+30 days'),
        ];
    }
}
