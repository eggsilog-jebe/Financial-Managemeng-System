<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Minimal Foundation Only (0 Bank Accounts, 0 Fiscal Periods, 0 Transactions)
        $this->call([
            UserAndRoleSeeder::class,
            ChartOfAccountsSeeder::class,
            TaxConfigurationSeeder::class,
        ]);
    }
}

