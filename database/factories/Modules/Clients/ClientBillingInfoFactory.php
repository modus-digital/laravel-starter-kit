<?php

declare(strict_types=1);

namespace Database\Factories\Modules\Clients;

use App\Models\Modules\Clients\Client;
use App\Models\Modules\Clients\ClientBillingInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ClientBillingInfo>
 */
final class ClientBillingInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'company' => $this->faker->company(),
            'tax_number' => $this->faker->numerify('TAX#######'),
            'vat_number' => $this->faker->numerify('VAT#######'),
            'address' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'city' => $this->faker->city(),
            'country' => $this->faker->countryCode(),
            'billing_email' => $this->faker->unique()->safeEmail(),
            'billing_phone' => $this->faker->phoneNumber(),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
