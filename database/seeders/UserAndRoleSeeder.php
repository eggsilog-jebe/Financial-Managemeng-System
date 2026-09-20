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
                'email'    => 'cashier@hospital.test',
                'name'     => 'Cashier',
                'role'     => 'Cashier',
                'password' => $password,
            ],
            [
                'email'    => 'billing@hospital.test',
                'name'     => 'Billing Clerk',
                'role'     => 'BillingClerk',
                'password' => $password,
            ],
            [
                'email'    => 'accountant@hospital.test',
                'name'     => 'Staff Accountant',
                'role'     => 'StaffAccountant',
                'password' => $password,
            ],
            [
                'email'    => 'manager@hospital.test',
                'name'     => 'Finance Manager',
                'role'     => 'FinanceManager',
                'password' => $password,
            ],
            [
                'email'    => 'auditor@hospital.test',
                'name'     => 'Internal Auditor',
                'role'     => 'Auditor',
                'password' => $password,
            ],
            [
                'email'    => 'cfo@hospital.test',
                'name'     => 'Chief Financial Officer',
                'role'     => 'CFO',
                'password' => $password,
            ],
            // Also seed .local aliases for backward compatibility
            [
                'email'    => 'cfo@hospital.local',
                'name'     => 'Chief Financial Officer',
                'role'     => 'CFO',
                'password' => $password,
            ],
            [
                'email'    => 'accountant@hospital.local',
                'name'     => 'Staff Accountant',
                'role'     => 'StaffAccountant',
                'password' => $password,
            ],
            [
                'email'    => 'cashier@hospital.local',
                'name'     => 'Cashier',
                'role'     => 'Cashier',
                'password' => $password,
            ],
            [
                'email'    => 'auditor@hospital.local',
                'name'     => 'Internal Auditor',
                'role'     => 'Auditor',
                'password' => $password,
            ],
            [
                'email'    => 'billing@hospital.local',
                'name'     => 'Billing Clerk',
                'role'     => 'BillingClerk',
                'password' => $password,
            ],
            [
                'email'    => 'manager@hospital.local',
                'name'     => 'Finance Manager',
                'role'     => 'FinanceManager',
                'password' => $password,
            ],
            // Enterprise Government Hospital Accounts (@hospital.gov.ph)
            [
                'email'    => 'cfo@hospital.gov.ph',
                'name'     => 'Chief Financial Officer',
                'role'     => 'CFO',
                'password' => $password,
            ],
            [
                'email'    => 'manager@hospital.gov.ph',
                'name'     => 'Finance Manager',
                'role'     => 'FinanceManager',
                'password' => $password,
            ],
            [
                'email'    => 'accountant@hospital.gov.ph',
                'name'     => 'Staff Accountant',
                'role'     => 'StaffAccountant',
                'password' => $password,
            ],
            [
                'email'    => 'billing@hospital.gov.ph',
                'name'     => 'Billing Clerk',
                'role'     => 'BillingClerk',
                'password' => $password,
            ],
            [
                'email'    => 'cashier@hospital.gov.ph',
                'name'     => 'Cashier',
                'role'     => 'Cashier',
                'password' => $password,
            ],
            [
                'email'    => 'auditor@hospital.gov.ph',
                'name'     => 'Internal Auditor',
                'role'     => 'Auditor',
                'password' => $password,
            ],
        ];

        foreach ($personas as $p) {
            User::updateOrCreate(['email' => $p['email']], $p);
        }
    }
}
