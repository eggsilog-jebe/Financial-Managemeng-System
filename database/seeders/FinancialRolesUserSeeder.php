<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class FinancialRolesUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email'    => 'cfo@hospital.local',
                'name'     => 'Dr. Roberto Garcia, CPA (Chief Financial Officer)',
                'role'     => 'CFO',
                'password' => Hash::make('password'),
            ],
            [
                'email'    => 'accountant@hospital.local',
                'name'     => 'Eduardo Mendoza, CPA (Senior Staff Accountant)',
                'role'     => 'StaffAccountant',
                'password' => Hash::make('password'),
            ],
            [
                'email'    => 'cashier@hospital.local',
                'name'     => 'Maria Santos (Cashier Desk Supervisor)',
                'role'     => 'Cashier',
                'password' => Hash::make('password'),
            ],
            [
                'email'    => 'auditor@hospital.local',
                'name'     => 'Atty. Cristina Gomez, CPA (External BIR CAS Auditor)',
                'role'     => 'Auditor',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                $u
            );
        }
    }
}
