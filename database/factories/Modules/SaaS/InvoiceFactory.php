<?php

namespace Database\Factories\Modules\SaaS;

use App\Models\Modules\Clients\Client;
use App\Models\Modules\SaaS\Invoice;
use App\Models\Modules\SaaS\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];

        return [
            'invoice_id' => 'INV-'.$this->faker->unique()->numerify('########'),
            'client_id' => Client::factory(),
            'subscription_id' => Subscription::factory(),
            'total' => $this->faker->randomFloat(2, 10.00, 1000.00),
            'currency' => $this->faker->randomElement($currencies),
            'status' => 'pending',
            'paid_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
        ];
    }
}
