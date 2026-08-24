<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\BankAccount;
use App\Models\PatientAccount;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubsystemIngestionApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_ingests_patient_bill_from_bdms_and_enforces_idempotency(): void
    {
        $patient = PatientAccount::factory()->create(['full_name' => 'Maria Dela Cruz']);

        $payload = [
            'patient_id'       => $patient->id,
            'bdms_bill_number' => 'BDMS-2026-0091',
            'invoice_date'     => now()->toDateString(),
            'gross_amount'     => 10000.00,
            'philhealth_amount'=> 3000.00,
            'hmo_amount'       => 2000.00,
            'hmo_provider'     => 'Maxicare',
            'discount_amount'  => 1000.00,
            'discount_type'    => 'SENIOR_CITIZEN',
            'id_card_number'   => 'OSCA-12345',
            'net_copay'        => 4000.00,
            'charge_lines'     => [
                [
                    'item_code'         => 'LAB-CBC',
                    'description'       => 'Complete Blood Count',
                    'department'        => 'LIS',
                    'revenue_category'  => 'CLINICAL',
                    'quantity'          => 1,
                    'unit_price'        => 5000.00,
                    'is_vatable'        => false,
                    'is_senior_eligible'=> true,
                ],
                [
                    'item_code'         => 'RAD-XRAY',
                    'description'       => 'Chest X-Ray',
                    'department'        => 'RIS',
                    'revenue_category'  => 'CLINICAL',
                    'quantity'          => 1,
                    'unit_price'        => 5000.00,
                    'is_vatable'        => false,
                    'is_senior_eligible'=> true,
                ],
            ],
        ];

        // 1. Initial Subsystem Ingestion Request
        $response1 = $this->withHeaders([
            'X-Idempotency-Key' => 'KEY-BDMS-998811',
            'Accept'            => 'application/json',
        ])->postJson('/api/v1/ingest/patient-bill', $payload);

        $response1->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'invoice_number', 'total_amount', 'status']]);

        // 2. Duplicate Idempotent Request with same key
        $response2 = $this->withHeaders([
            'X-Idempotency-Key' => 'KEY-BDMS-998811',
            'Accept'            => 'application/json',
        ])->postJson('/api/v1/ingest/patient-bill', $payload);

        $response2->assertStatus(201)
            ->assertHeader('X-Idempotency-Replay', 'true');
    }

    /** @test */
    public function test_it_ingests_vendor_bill_from_psm(): void
    {
        $vendor = Vendor::create([
            'code' => 'VEND-001',
            'name' => 'MedSupply Inc.',
            'tin'  => '123-456-789-000',
            'status' => 'Active',
        ]);

        $payload = [
            'vendor_id'             => $vendor->id,
            'po_number'             => 'PO-2026-100',
            'grn_reference'         => 'GRN-2026-100',
            'vendor_invoice_number' => 'SI-MED-5544',
            'bill_date'             => now()->toDateString(),
            'due_date'              => now()->addDays(30)->toDateString(),
            'invoice_amount'        => 50000.00,
            'ewt_rate'              => 0.01,
            'atc_code'              => 'WI158',
            'items'                 => [
                [
                    'item_code'    => 'SUP-GLOVES',
                    'description'  => 'Surgical Gloves Box',
                    'expense_type' => 'GOODS_INVENTORY',
                    'quantity'     => 100,
                    'unit_price'   => 500.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/ingest/vendor-bill', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'bill_number', 'total_amount', 'status']]);
    }

    /** @test */
    public function test_it_ingests_payroll_register_from_hrms(): void
    {
        $bank = BankAccount::create([
            'name' => 'Metrobank Operating Account',
            'bank_name' => 'Metrobank Medical Center Branch',
            'account_number' => '1234-5678-90',
            'gl_code' => '1020',
            'purpose' => 'Operating',
            'currency' => 'PHP',
            'balance' => 500000.00,
            'status' => 'Active',
        ]);

        $payload = [
            'cutoff_start'                 => '2026-08-01',
            'cutoff_end'                   => '2026-08-15',
            'payout_date'                  => '2026-08-15',
            'disbursement_bank_account_id' => $bank->id,
            'total_gross_pay'              => 80000.00,
            'total_net_pay'                => 70000.00,
            'total_sss_employee'           => 1800.00,
            'total_sss_employer'           => 3800.00,
            'total_philhealth_employee'    => 2000.00,
            'total_philhealth_employer'    => 2000.00,
            'total_pagibig_employee'       => 200.00,
            'total_pagibig_employer'       => 200.00,
            'total_withholding_tax_1601c'  => 6000.00,
            'employees'                    => [
                [
                    'employee_id_number' => 'EMP-RN-101',
                    'employee_name'      => 'Nurse Florence',
                    'department'         => 'NURSING',
                    'basic_salary'       => 80000.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/ingest/payroll-register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'payroll_run_number', 'total_gross_pay', 'status']]);
    }
}
