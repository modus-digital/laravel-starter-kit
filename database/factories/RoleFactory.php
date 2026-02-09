<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
final class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->slug(2),
            'guard_name' => 'web',
            'internal' => false,
        ];
    }

    /**
     * Indicate that the role is internal.
     */
    public function internal(): static
    {
        return $this->state(fn (array $attributes): array => [
            'internal' => true,
        ]);
    }

    /**
     * Indicate that the role is external.
     */
    public function external(): static
    {
        return $this->state(fn (array $attributes): array => [
            'internal' => false,
        ]);
    }
}
