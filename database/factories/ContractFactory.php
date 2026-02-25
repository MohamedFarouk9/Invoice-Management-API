<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'tenant_id' => 1,
            'unit_name' => fake()->word() . ' ' . fake()->randomNumber(3),
            'customer_name' => fake()->name(),
            'rent_amount' => fake()->numberBetween(1000, 5000),
            'start_date' => $startDate,
            'end_date' => fake()->dateTimeBetween($startDate, '+2 years'),
            'status' => ContractStatus::ACTIVE,
        ];
    }
}
