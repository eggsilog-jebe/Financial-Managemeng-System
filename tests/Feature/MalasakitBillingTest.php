<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\GuaranteeLetter;
use App\Models\PatientAccount;
use App\Models\User;
use App\Services\Accounting\MalasakitBillComputationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MalasakitBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_malasakit_registry_renders_for_billing_clerk(): void
    {
        $clerk = User::factory()->create([
            'email' => 'billing@hospital.gov.ph',
            'role'  => 'BillingClerk',
        ]);

        $response = $this->actingAs($clerk)->get(route('ar.malasakit.index'));

        $response->assertStatus(200);
        $response->assertSee('Malasakit Center Financial Assistance');
    }

    public function test_statutory_discount_calculation_applies_vat_exemption_and_twenty_percent(): void
    {
        $patient = PatientAccount::factory()->create([
            'full_name'         => 'Test Senior Citizen',
            'discount_category' => 'SENIOR_CITIZEN',
            'is_nbb'            => false,
        ]);

        $service = app(MalasakitBillComputationService::class);

        // ₱112,000 Gross bill (Vatable)
        // Step 1: VAT Relief = ₱112,000 - (112,000 / 1.12) = ₱12,000
        // Step 2: 20% SC Discount on ₱100,000 Net of VAT = ₱20,000
        // Balance after discounts = ₱80,000
        // PhilHealth Primary Case Rate = ₱30,000
        // Net out-of-pocket = ₱50,000
        $result = $service->computeWaterfall(
            patient: $patient,
            items: [
                [
                    'description' => 'Inpatient Medical Stay',
                    'quantity'    => 1,
                    'unit_price'  => 112000,
                    'is_vatable'  => true,
                    'is_eligible' => true,
                ],
            ],
            philhealthPrimaryCaseRate: '30000.0000'
        );

        $this->assertEquals('112000.0000', $result['gross_total']);
        $this->assertEquals('12000.0000', $result['vat_exempt_relief']);
        $this->assertEquals('20000.0000', $result['statutory_discount']);
        $this->assertEquals('80000.0000', $result['amount_after_discounts']);
        $this->assertEquals('30000.0000', $result['philhealth_deduction']);
        $this->assertEquals('50000.0000', $result['net_patient_payable']);
    }

    public function test_no_balance_billing_zeros_out_indigent_patient_out_of_pocket(): void
    {
        $indigent = PatientAccount::factory()->create([
            'full_name'         => 'Test Indigent Patient',
            'patient_type'      => 'indigent',
            'discount_category' => 'CHARITY',
            'is_nbb'            => true,
        ]);

        $service = app(MalasakitBillComputationService::class);

        $result = $service->computeWaterfall(
            patient: $indigent,
            items: [
                [
                    'description' => 'Emergency Ward Care',
                    'quantity'    => 1,
                    'unit_price'  => 65000,
                    'is_vatable'  => false,
                    'is_eligible' => true,
                ],
            ],
            philhealthPrimaryCaseRate: '25000.0000'
        );

        $this->assertTrue($result['is_nbb_covered']);
        $this->assertEquals('0.0000', $result['net_patient_payable']);
        $this->assertEquals('40000.0000', $result['nbb_subsidy_amount']); // 65000 Gross - PH 25000 = 40000 absorbed under NBB
    }

    public function test_guarantee_letter_reduces_patient_payable_balance(): void
    {
        $patient = PatientAccount::factory()->create([
            'full_name'         => 'Test PWD Patient',
            'discount_category' => 'PWD',
            'is_nbb'            => false,
        ]);

        $gl = GuaranteeLetter::factory()->create([
            'gl_number'          => 'PCSO-TEST-GL-001',
            'issuing_agency'     => 'PCSO',
            'patient_account_id' => $patient->id,
            'authorized_amount'  => '20000.0000',
            'utilized_amount'    => '0.0000',
            'remaining_amount'   => '20000.0000',
            'status'             => 'ACTIVE',
        ]);

        $service = app(MalasakitBillComputationService::class);

        $result = $service->computeWaterfall(
            patient: $patient,
            items: [
                [
                    'description' => 'Surgical Ward',
                    'quantity'    => 1,
                    'unit_price'  => 50000,
                    'is_vatable'  => false,
                    'is_eligible' => true,
                ],
            ],
            philhealthPrimaryCaseRate: '15000.0000',
            applicableGuaranteeLetters: collect([$gl])
        );

        // 50000 gross - 10000 PWD 20% = 40000
        // 40000 - 15000 PH = 25000
        // 25000 - 20000 PCSO GL = 5000
        $this->assertEquals('5000.0000', $result['net_patient_payable']);
        $this->assertCount(1, $result['guarantee_letters_applied']);
        $this->assertEquals('PCSO', $result['guarantee_letters_applied'][0]['agency']);
    }

    public function test_guarantee_letter_registration_endpoint_stores_and_records_audit(): void
    {
        $clerk = User::factory()->create([
            'email' => 'billing@hospital.gov.ph',
            'role'  => 'BillingClerk',
        ]);

        $patient = PatientAccount::factory()->create([
            'full_name' => 'Admitted Patient',
        ]);

        $response = $this->actingAs($clerk)->post(route('ar.malasakit.store'), [
            'gl_number'          => 'DSWD-AICS-TEST-999',
            'issuing_agency'     => 'DSWD',
            'patient_account_id' => $patient->id,
            'authorized_amount'  => 18500,
            'issued_date'        => now()->toDateString(),
            'diagnosis'          => 'Emergency Admission',
        ]);

        $response->assertRedirect(route('ar.malasakit.index'));

        $this->assertDatabaseHas('guarantee_letters', [
            'gl_number'      => 'DSWD-AICS-TEST-999',
            'issuing_agency' => 'DSWD',
        ]);

        // Audit log observer verification
        $this->assertDatabaseHas('activity_logs', [
            'event'  => 'created',
            'module' => 'Malasakit & Subsidies',
        ]);
    }
}
