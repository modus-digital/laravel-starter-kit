<?php

namespace Database\Factories\Modules\SaaS;

use App\Models\Modules\SaaS\Invoice;
use App\Models\Modules\SaaS\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providers = ['stripe', 'paypal', 'bank_transfer', 'credit_card'];
        $currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];

        return [
            'invoice_id' => Invoice::factory(),
            'provider' => $this->faker->randomElement($providers),
            'provider_payment_id' => $this->faker->optional()->uuid(),
            'amount' => $this->faker->randomFloat(2, 10.00, 1000.00),
            'currency' => $this->faker->randomElement($currencies),
            'status' => 'pending',
            'paid_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
