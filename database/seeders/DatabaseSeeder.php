<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@hospital.fms')->doesntExist()) {
            User::factory()->create([
                'name' => 'Chief Accountant',
                'email' => 'admin@hospital.fms',
            ]);
        }

        $this->call(HospitalFmsSeeder::class);
    }
}
