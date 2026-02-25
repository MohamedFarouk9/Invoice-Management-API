<?php

namespace App\Tax;

/**
 * VAT Calculator.
 * Calculates 15% of the subtotal.
 */
class VatTaxCalculator implements TaxCalculatorInterface
{
    private const VAT_RATE = 0.15;

    public function calculate(float $subtotal): float
    {
        return round($subtotal * self::VAT_RATE, 2);
    }

    public function getName(): string
    {
        return 'vat';
    }
}
