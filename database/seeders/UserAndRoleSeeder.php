<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserAndRoleSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $personas = [
            [
                'email'    => 'g59kamikaze@gmail.com',
                'name'     => 'Chief Financial Officer',
                'role'     => 'CFO',
                'password' => Hash::make('@Sonorous_XVI'),
                'status'   => 'active',
            ],
            [
                'email'    => 'cfo@hospital.gov.ph',
                'name'     => 'Chief Financial Officer',
                'role'     => 'CFO',
                'password' => $password,
                'status'   => 'active',
            ],
            [
                'email'    => 'manager@hospital.gov.ph',
                'name'     => 'Finance Manager',
                'role'     => 'FinanceManager',
                'password' => $password,
                'status'   => 'active',
            ],
            [
                'email'    => 'accountant@hospital.gov.ph',
                'name'     => 'Staff Accountant',
                'role'     => 'StaffAccountant',
                'password' => $password,
                'status'   => 'active',
            ],
            [
                'email'    => 'billing@hospital.gov.ph',
                'name'     => 'Billing Clerk',
                'role'     => 'BillingClerk',
                'password' => $password,
                'status'   => 'active',
            ],
            [
                'email'    => 'cashier@hospital.gov.ph',
                'name'     => 'Cashier',
                'role'     => 'Cashier',
                'password' => $password,
                'status'   => 'active',
            ],
            [
                'email'    => 'auditor@hospital.gov.ph',
                'name'     => 'Internal Auditor',
                'role'     => 'Auditor',
                'password' => $password,
                'status'   => 'active',
            ],
        ];

        foreach ($personas as $p) {
            User::updateOrCreate(['email' => $p['email']], $p);
        }
    }
}
