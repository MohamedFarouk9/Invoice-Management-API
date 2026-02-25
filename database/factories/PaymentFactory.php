<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        $invoice = Invoice::factory()->create();

        return [
            'invoice_id' => $invoice->id,
            'amount' => fake()->numberBetween(100, 1000),
            'payment_method' => fake()->randomElement([
                PaymentMethod::CASH->value,
                PaymentMethod::BANK_TRANSFER->value,
                PaymentMethod::CREDIT_CARD->value,
            ]),
            'reference_number' => 'TXN-' . fake()->randomNumber(6),
            'paid_at' => now(),
        ];
    }
}
