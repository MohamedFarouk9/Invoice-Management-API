<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes automatically prefixed with /api
| All routes require 'auth:sanctum' middleware (or your auth scheme)
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Invoice endpoints
    Route::prefix('contracts/{contract}')->group(function () {
        // Create invoice for contract
        Route::post('invoices', [InvoiceController::class, 'store'])
            ->name('invoices.store');

        // List invoices for contract (with pagination & filtering)
        Route::get('invoices', [InvoiceController::class, 'index'])
            ->name('invoices.index');

        // Get financial summary for contract
        Route::get('summary', [InvoiceController::class, 'summary'])
            ->name('contracts.summary');
    });

    // Invoice detail and payment endpoints
    Route::prefix('invoices/{invoice}')->group(function () {
        // Get invoice details with payments
        Route::get('/', [InvoiceController::class, 'show'])
            ->name('invoices.show');

        // Record payment against invoice
        Route::post('payments', [InvoiceController::class, 'recordPayment'])
            ->name('payments.store');
    });
});
