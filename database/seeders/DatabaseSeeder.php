<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Creates realistic test data:
     * - 3 Tenants
     * - 6 Users (2 per tenant)
     * - 15 Contracts (5 per tenant)
     * - ~33 Invoices
     * - ~54 Payments
     * 
     * Run: php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // Seed 3 tenants with realistic data
        $this->seedTenant(1, 'Tenant Alpha');
        $this->seedTenant(2, 'Tenant Beta');
        $this->seedTenant(3, 'Tenant Gamma');

        $this->command->info('🎉 Database seeded successfully!');
    }

    /**
     * Seed a single tenant with users, contracts, invoices, and payments.
     * 
     * @param int $tenantId The tenant ID
     * @param string $tenantName The tenant name (for display)
     */
    private function seedTenant(int $tenantId, string $tenantName): void
    {
        $this->command->info("\n🌱 Seeding {$tenantName} (ID: {$tenantId})...");

        // Create 2 users per tenant
        $users = User::factory(2)
            ->create(['tenant_id' => $tenantId]);
        $this->command->line("   ✓ Created {$users->count()} users");

        // Create 5 contracts per tenant
        $contracts = Contract::factory(5)
            ->create(['tenant_id' => $tenantId]);
        $this->command->line("   ✓ Created {$contracts->count()} contracts");

        // Track totals for display
        $invoicesTotal = 0;
        $paymentsTotal = 0;

        // For each contract, create invoices and payments
        foreach ($contracts as $contract) {
            // Create 2-4 invoices per contract
            $invoiceCount = rand(2, 4);
            $invoices = Invoice::factory($invoiceCount)
                ->for($contract)
                ->create(['tenant_id' => $tenantId]);

            $invoicesTotal += $invoiceCount;

            // For each invoice, create 0-2 payments
            foreach ($invoices as $invoice) {
                $paymentCount = rand(0, 2);

                if ($paymentCount > 0) {
                    // Create payments
                    $payments = Payment::factory($paymentCount)
                        ->for($invoice)
                        ->create();

                    $paymentsTotal += $paymentCount;

                    // Update invoice status based on total payments
                    $totalPaid = $invoice->payments->sum('amount');

                    if ($totalPaid >= $invoice->total) {
                        // Full payment made
                        $invoice->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    } elseif ($totalPaid > 0) {
                        // Partial payment made
                        $invoice->update([
                            'status' => 'partially_paid',
                        ]);
                    }
                }
            }
        }

        $this->command->line("   ✓ Created {$invoicesTotal} invoices");
        $this->command->line("   ✓ Created {$paymentsTotal} payments");
        $this->command->info("   ✅ {$tenantName} seeded successfully!");
    }
}
