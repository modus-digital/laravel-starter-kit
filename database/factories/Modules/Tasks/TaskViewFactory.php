<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Tasks;

use App\Enums\Modules\Tasks\TaskViewType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Tasks\TaskView>
 */
final class TaskViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'taskable_type' => User::class,
            'taskable_id' => User::factory(),
            'is_default' => false,
            'name' => $this->faker->words(2, true),
            'slug' => Str::slug($this->faker->unique()->uuid()),
            'type' => TaskViewType::LIST,
            'metadata' => null,
        ];
    }
}
