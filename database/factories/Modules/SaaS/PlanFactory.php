<?php

declare(strict_types=1);

namespace Database\Factories\Modules\SaaS;

use App\Enums\ActivityStatus;
use App\Models\Modules\SaaS\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Plan>
 */
final class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $intervals = ['month', 'year', 'week', 'day'];

        return [
            'name' => $this->faker->words(3, true).' Plan',
            'price' => $this->faker->randomFloat(2, 9.99, 999.99),
            'interval' => $this->faker->randomElement($intervals),
            'trial_days' => $this->faker->optional()->numberBetween(7, 30),
            'features' => [
                'feature1' => $this->faker->sentence(),
                'feature2' => $this->faker->sentence(),
                'feature3' => $this->faker->sentence(),
            ],
            'status' => ActivityStatus::ACTIVE,
        ];
    }
}
