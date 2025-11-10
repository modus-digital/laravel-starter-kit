<?php

declare(strict_types=1);

namespace ModusDigital\Clients\Database\Factories;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use ModusDigital\Clients\Models\Client;

final class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'status' => ActivityStatus::ACTIVE,
            'website' => fake()->url(),
        ];
    }
}
