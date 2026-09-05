<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BankAccount;
use Illuminate\Database\Seeder;

final class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        $glAccount = Account::where('code', '1020')->first();

        BankAccount::firstOrCreate(
            ['account_number' => 'MBTC-OPER-001020'],
            [
                'name'            => 'Metrobank Main Operating Account',
                'bank_name'       => 'Metropolitan Bank & Trust Co.',
                'account_number'  => 'MBTC-OPER-001020',
                'gl_code'         => '1020',
                'gl_account_id'   => $glAccount?->id,
                'purpose'         => 'General Operating & Disbursements',
                'currency'        => 'PHP',
                'opening_balance' => 5000000.0000,
                'balance'         => 5000000.0000,
                'minimum_balance' => 50000.0000,
                'status'          => 'Active',
                'is_active'       => true,
            ]
        );
    }
}
