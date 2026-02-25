<?php

namespace App\Services;

use App\Tax\TaxCalculatorInterface;

class TaxService
{
    private array $calculators = [];

    /**
     * Constructor accepts multiple tax calculators.
     */
    public function __construct(TaxCalculatorInterface ...$calculators)
    {
        $this->calculators = $calculators;
    }


    public function calculate(float $subtotal): float
    {
        $totalTax = 0.0;

        foreach ($this->calculators as $calculator) {
            $totalTax += $calculator->calculate($subtotal);
        }

        return round($totalTax, 2);
    }

    public function getBreakdown(float $subtotal): array
    {
        $breakdown = [];

        foreach ($this->calculators as $calculator) {
            $breakdown[$calculator->getName()] = $calculator->calculate($subtotal);
        }

        return $breakdown;
    }

    /**
     * for testing.
     */
    public function addCalculator(TaxCalculatorInterface $calculator): void
    {
        $this->calculators[] = $calculator;
    }

    public function getCalculators(): array
    {
        return $this->calculators;
    }
}
