<?php

namespace App\Providers;

use App\Services\TaxService;
use App\Tax\MunicipalFeeTaxCalculator;
use App\Tax\VatTaxCalculator;
use Illuminate\Support\ServiceProvider;

/**
 * Adding a new tax type requires:
 * 1. Create new calculator class
 * 2. Add it here in register()
 */
class TaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaxService::class, function ($app) {
            return new TaxService(
                new VatTaxCalculator(),
                new MunicipalFeeTaxCalculator(),
                // Future taxes are added here without modifying anything else:
                // new TourismTaxCalculator(),
                // new StateTaxCalculator(),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
