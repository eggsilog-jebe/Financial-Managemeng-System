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
            'date_of_birth'     => fake()->dateTimeBetween('-75 years', '-18 years')->format('Y-m-d'),
            'gender'            => fake()->randomElement(['Male', 'Female']),
            'admission_type'    => fake()->randomElement(['INPATIENT', 'OUTPATIENT', 'EMERGENCY']),
            'discount_category' => fake()->randomElement(['NONE', 'SENIOR_CITIZEN', 'PWD', 'EMPLOYEE_SUBSIDY']),
            'id_card_number'    => 'ID-' . fake()->numerify('#####'),
            'phone'             => '+63 917 ' . fake()->numerify('### ####'),
            'email'             => fake()->safeEmail(),
            'address'           => fake()->address(),
            'hmo_provider'      => fake()->optional()->randomElement([
                'Maxicare Healthcare Corp',
                'Asalus Corporation (Intellicare)',
                'Medicard Philippines',
                'PhilCare',
                'Etiqa Life and General',
                'Carehealth Plus',
                'Pacific Cross',
                'Insular Health Care (InLife Health Care)',
                'Caritas Health Shield',
            ]),
            'total_billed'      => '0.0000',
            'current_balance'   => '0.0000',
            'status'            => 'Active',
        ];
    }
}
