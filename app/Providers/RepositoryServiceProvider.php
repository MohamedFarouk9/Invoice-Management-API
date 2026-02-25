<?php

namespace App\Providers;

use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Contracts\EloquentContractRepository;
use App\Repositories\Invoices\EloquentInvoiceRepository;
use App\Repositories\Invoices\InvoiceRepositoryInterface;
use App\Repositories\Payments\EloquentPaymentRepository;
use App\Repositories\Payments\PaymentRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider.
 * Binds all repository interfaces to their implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Contract Repository
        $this->app->bind(
            ContractRepositoryInterface::class,
            EloquentContractRepository::class,
        );

        // Invoice Repository
        $this->app->bind(
            InvoiceRepositoryInterface::class,
            EloquentInvoiceRepository::class,
        );

        // Payment Repository
        $this->app->bind(
            PaymentRepositoryInterface::class,
            EloquentPaymentRepository::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
