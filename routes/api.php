<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All routes automatically prefixed with /api
|
*/

// ============================================
// Contract Routes (POST/GET invoices & summary)
// ============================================
Route::prefix('contracts/{contract}')->group(function () {
    // POST - Create invoice for contract
    Route::post('invoices', [InvoiceController::class, 'store'])
        ->name('invoices.store');

    // GET - List invoices for contract (with pagination & filtering)
    Route::get('invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index');

    // GET - Get financial summary for contract
    Route::get('summary', [InvoiceController::class, 'summary'])
        ->name('contracts.summary');
});

// ============================================
// Invoice Routes (GET details & POST payments)
// ============================================
Route::prefix('invoices/{invoice}')->group(function () {
    // GET - Get invoice details with payments
    Route::get('/', [InvoiceController::class, 'show'])
        ->name('invoices.show');

    // POST - Record payment against invoice
    Route::post('payments', [InvoiceController::class, 'recordPayment'])
        ->name('payments.store');
});
