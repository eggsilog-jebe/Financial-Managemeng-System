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
        $password = Hash::make('password123');

        $personas = [
            [
                'email'    => 'cashier@hospital.test',
                'name'     => 'Maria Santos (Cashier Officer)',
                'role'     => 'Cashier',
                'password' => $password,
            ],
            [
                'email'    => 'billing@hospital.test',
                'name'     => 'Clara Reyes (Billing Clerk)',
                'role'     => 'BillingClerk',
                'password' => $password,
            ],
            [
                'email'    => 'accountant@hospital.test',
                'name'     => 'Eduardo Mendoza, CPA (Staff Accountant)',
                'role'     => 'StaffAccountant',
                'password' => $password,
            ],
            [
                'email'    => 'manager@hospital.test',
                'name'     => 'Patricia Villanueva, CPA (Finance Manager)',
                'role'     => 'FinanceManager',
                'password' => $password,
            ],
            [
                'email'    => 'auditor@hospital.test',
                'name'     => 'Atty. Cristina Gomez, CPA (Internal Auditor)',
                'role'     => 'Auditor',
                'password' => $password,
            ],
            [
                'email'    => 'cfo@hospital.test',
                'name'     => 'Dr. Roberto Garcia, CPA (Chief Financial Officer)',
                'role'     => 'CFO',
                'password' => $password,
            ],
            // Also seed .local aliases for backward compatibility with existing demo buttons
            [
                'email'    => 'cfo@hospital.local',
                'name'     => 'Dr. Roberto Garcia, CPA (Chief Financial Officer)',
                'role'     => 'CFO',
                'password' => $password,
            ],
            [
                'email'    => 'accountant@hospital.local',
                'name'     => 'Eduardo Mendoza, CPA (Staff Accountant)',
                'role'     => 'StaffAccountant',
                'password' => $password,
            ],
            [
                'email'    => 'cashier@hospital.local',
                'name'     => 'Maria Santos (Cashier Desk)',
                'role'     => 'Cashier',
                'password' => $password,
            ],
            [
                'email'    => 'auditor@hospital.local',
                'name'     => 'Atty. Cristina Gomez, CPA (Auditor)',
                'role'     => 'Auditor',
                'password' => $password,
            ],
            [
                'email'    => 'billing@hospital.local',
                'name'     => 'Clara Reyes (Billing Clerk)',
                'role'     => 'BillingClerk',
                'password' => $password,
            ],
            [
                'email'    => 'manager@hospital.local',
                'name'     => 'Patricia Villanueva, CPA (Finance Manager)',
                'role'     => 'FinanceManager',
                'password' => $password,
            ],
        ];

        foreach ($personas as $p) {
            User::updateOrCreate(['email' => $p['email']], $p);
        }
    }
}
