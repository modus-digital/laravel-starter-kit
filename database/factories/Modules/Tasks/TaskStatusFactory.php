<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Tasks;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Tasks\TaskStatus>
 */
final class TaskStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
        ];
    }
}
