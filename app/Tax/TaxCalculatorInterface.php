<?php

namespace App\Tax;


interface TaxCalculatorInterface
{
    // Calculate tax amount for a subtotal.
    public function calculate(float $subtotal): float;

    // Get the tax name.
    public function getName(): string;
}
