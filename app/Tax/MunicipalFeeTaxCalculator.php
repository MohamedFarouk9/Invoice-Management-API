<?php

namespace App\Tax;

/**
 * Municipal Fee Calculator.
 * Calculates 2.5% of the subtotal.
 */

class MunicipalFeeTaxCalculator implements TaxCalculatorInterface
{
    private const MUNICIPAL_RATE = 0.025;

    public function calculate(float $subtotal): float
    {
        return round($subtotal * self::MUNICIPAL_RATE, 2);
    }

     public function getName(): string
    {
        return 'municipal_fee';
    }
}
