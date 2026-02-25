<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $contract = Contract::factory()->create();
        $subtotal = fake()->numberBetween(500, 2000);
        $tax = round($subtotal * 0.175, 2); // 15% VAT + 2.5% Municipal

        return [
            'tenant_id' => 1,
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-001-' . date('Ym') . '-' . fake()->randomNumber(4),
            'subtotal' => $subtotal,
            'tax_amount' => $tax,
            'total' => $subtotal + $tax,
            'status' => InvoiceStatus::PENDING,
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'paid_at' => null,
        ];
    }
}
