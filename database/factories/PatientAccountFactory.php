<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PatientAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PatientAccount>
 */
class PatientAccountFactory extends Factory
{
    protected $model = PatientAccount::class;

    public function definition(): array
    {
        return [
            'patient_id_number' => 'PAT-' . fake()->unique()->numerify('#####'),
            'full_name'         => fake()->name(),
            'admission_type'    => fake()->randomElement(['Inpatient', 'Outpatient', 'Emergency']),
            'hmo_provider'      => fake()->optional()->randomElement(['Maxicare', 'Medicard', 'PhilCare', 'Intellicare']),
            'total_billed'      => '0.0000',
            'current_balance'   => '0.0000',
            'status'            => 'Active',
        ];
    }
}
