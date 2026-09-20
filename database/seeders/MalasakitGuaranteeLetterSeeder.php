<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GuaranteeLetter;
use App\Models\PatientAccount;
use App\Models\User;
use Illuminate\Database\Seeder;

final class MalasakitGuaranteeLetterSeeder extends Seeder
{
    public function run(): void
    {
        $cfoUser = User::where('role', 'CFO')->first();

        // 1. Ensure representative public hospital patient profiles exist
        $patient1 = PatientAccount::updateOrCreate(
            ['patient_id_number' => 'MRN-2026-0001'],
            [
                'full_name'              => 'Maria Corazon Santos',
                'date_of_birth'          => '1958-04-12',
                'gender'                 => 'Female',
                'admission_type'         => 'INPATIENT',
                'patient_type'           => 'senior_citizen',
                'discount_category'      => 'SENIOR_CITIZEN',
                'id_card_number'         => 'OSCA-NCR-2026-88912',
                'is_nbb'                 => false,
                'philhealth_member_type' => 'lifetime',
                'philhealth_id_number'   => '12-050412891-3',
                'status'                 => 'Active',
            ]
        );

        $patient2 = PatientAccount::updateOrCreate(
            ['patient_id_number' => 'MRN-2026-0002'],
            [
                'full_name'              => 'Juanito Dela Cruz',
                'date_of_birth'          => '1974-08-25',
                'gender'                 => 'Male',
                'admission_type'         => 'INPATIENT',
                'patient_type'           => 'indigent',
                'discount_category'      => 'CHARITY',
                'id_card_number'         => '4PS-NCR-QC-54210',
                'is_nbb'                 => true, // No Balance Billing (Ward Accommodation)
                'philhealth_member_type' => '4ps',
                'philhealth_id_number'   => '12-384910294-8',
                'status'                 => 'Active',
            ]
        );

        $patient3 = PatientAccount::updateOrCreate(
            ['patient_id_number' => 'MRN-2026-0003'],
            [
                'full_name'              => 'Elena Bautista',
                'date_of_birth'          => '1989-11-03',
                'gender'                 => 'Female',
                'admission_type'         => 'INPATIENT',
                'patient_type'           => 'malasakit',
                'discount_category'      => 'PWD',
                'id_card_number'         => 'NCDA-PWD-2026-10492',
                'is_nbb'                 => false,
                'philhealth_member_type' => 'regular',
                'philhealth_id_number'   => '12-992837482-1',
                'status'                 => 'Active',
            ]
        );

        $patient4 = PatientAccount::updateOrCreate(
            ['patient_id_number' => 'MRN-2026-0004'],
            [
                'full_name'              => 'Baby Joshua Fernandez',
                'date_of_birth'          => '2025-06-15',
                'gender'                 => 'Male',
                'admission_type'         => 'INPATIENT',
                'patient_type'           => 'malasakit',
                'discount_category'      => 'NONE',
                'id_card_number'         => null,
                'is_nbb'                 => true,
                'philhealth_member_type' => 'sponsored',
                'philhealth_id_number'   => '12-401928371-5',
                'status'                 => 'Active',
            ]
        );

        // 2. Seed active Guarantee Letters (PCSO, DSWD, DOH-MAIP)
        $gls = [
            [
                'gl_number'          => 'PCSO-GL-2026-00812',
                'issuing_agency'     => 'PCSO',
                'patient_account_id' => $patient3->id,
                'authorized_amount'  => '45000.0000',
                'utilized_amount'    => '0.0000',
                'remaining_amount'   => '45000.0000',
                'status'             => 'ACTIVE',
                'issued_date'        => now()->subDays(5)->toDateString(),
                'valid_until'        => now()->addDays(55)->toDateString(),
                'diagnosis'          => 'Post-Operative Orthopedic Fixation / Implant Support',
                'remarks'            => 'Approved under PCSO Individual Medical Assistance Program (IMAP).',
                'created_by'         => $cfoUser?->id,
            ],
            [
                'gl_number'          => 'DSWD-AICS-2026-0341',
                'issuing_agency'     => 'DSWD',
                'patient_account_id' => $patient3->id,
                'authorized_amount'  => '15000.0000',
                'utilized_amount'    => '0.0000',
                'remaining_amount'   => '15000.0000',
                'status'             => 'ACTIVE',
                'issued_date'        => now()->subDays(3)->toDateString(),
                'valid_until'        => now()->addDays(27)->toDateString(),
                'diagnosis'          => 'Medical & Pharmacy Assistance for Surgical Supplies',
                'remarks'            => 'Crisis Intervention Section AICS Financial Grant.',
                'created_by'         => $cfoUser?->id,
            ],
            [
                'gl_number'          => 'DOH-MAIP-2026-1104',
                'issuing_agency'     => 'DOH_MAIP',
                'patient_account_id' => $patient4->id,
                'authorized_amount'  => '30000.0000',
                'utilized_amount'    => '0.0000',
                'remaining_amount'   => '30000.0000',
                'status'             => 'ACTIVE',
                'issued_date'        => now()->subDays(2)->toDateString(),
                'valid_until'        => now()->addDays(58)->toDateString(),
                'diagnosis'          => 'Severe Pediatric Pneumonia / NICU Phototherapy',
                'remarks'            => 'Medical Assistance for Indigent and Financially Incapacitated Patients (MAIFIP).',
                'created_by'         => $cfoUser?->id,
            ],
            [
                'gl_number'          => 'LGU-QC-MAYOR-2026-092',
                'issuing_agency'     => 'LGU',
                'patient_account_id' => $patient1->id,
                'authorized_amount'  => '10000.0000',
                'utilized_amount'    => '0.0000',
                'remaining_amount'   => '10000.0000',
                'status'             => 'ACTIVE',
                'issued_date'        => now()->subDays(7)->toDateString(),
                'valid_until'        => now()->addDays(23)->toDateString(),
                'diagnosis'          => 'Chronic Kidney Disease Hemodialysis Support',
                'remarks'            => 'City Social Services Development Department Assistance.',
                'created_by'         => $cfoUser?->id,
            ],
        ];

        foreach ($gls as $glData) {
            GuaranteeLetter::updateOrCreate(
                ['gl_number' => $glData['gl_number']],
                $glData
            );
        }
    }
}
