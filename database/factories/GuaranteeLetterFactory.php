<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GuaranteeLetter;
use App\Models\PatientAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuaranteeLetter>
 */
class GuaranteeLetterFactory extends Factory
{
    protected $model = GuaranteeLetter::class;

    public function definition(): array
    {
        $amount = (string) fake()->randomElement(['10000.0000', '25000.0000', '50000.0000']);

        return [
            'gl_number'          => 'GL-' . fake()->unique()->numerify('####-#####'),
            'issuing_agency'     => fake()->randomElement(['PCSO', 'DSWD', 'DOH_MAIP', 'LGU']),
            'patient_account_id' => PatientAccount::factory(),
            'authorized_amount'  => $amount,
            'utilized_amount'    => '0.0000',
            'remaining_amount'   => $amount,
            'status'             => 'ACTIVE',
            'issued_date'        => now()->toDateString(),
            'valid_until'        => now()->addDays(60)->toDateString(),
            'diagnosis'          => 'Inpatient Medical Care',
            'remarks'            => 'Government medical assistance subsidy.',
        ];
    }
}
