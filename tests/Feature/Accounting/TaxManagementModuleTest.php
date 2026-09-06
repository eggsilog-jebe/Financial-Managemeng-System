<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Bir2307Certificate;
use App\Models\CasAuditTrail;
use App\Models\FiscalPeriod;
use App\Models\OfficialReceipt;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;
use App\Models\StatutoryDiscount;
use App\Models\TaxCertificate;
use App\Models\TaxReturn;
use App\Models\TaxRule;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Accounting\BirTaxScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaxManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private User $manager;
    private User $cfo;
    private User $cashier;
    private BirTaxScheduleService $taxScheduleService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Tax Accountant', 'email' => 'tax.accountant@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'finance.manager@hospital.local']);
        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'Chief Financial Officer', 'email' => 'cfo@hospital.local']);
        $this->cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Frontdesk Cashier', 'email' => 'cashier@hospital.local']);

        FiscalPeriod::create([
            'period_code'   => '2026-FY',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);

        $this->taxScheduleService = app(BirTaxScheduleService::class);
    }

    /** @test */
    public function test_bir_tax_schedule_service_calculates_bir_1601_eq_summary_correctly(): void
    {
        $vendor = Vendor::create([
            'vendor_code'      => 'VND-001',
            'name'             => 'Apex Medical Supplies Inc',
            'tin'              => '102-334-556-000',
            'tax_type'         => 'VAT_REGISTERED',
            'default_ewt_rate' => 0.0100,
            'default_atc_code' => 'WI158',
            'status'           => 'ACTIVE',
        ]);

        $bill = PurchaseBill::create([
            'bill_number'  => 'PB-2026-001',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-08-01',
            'due_date'     => '2026-08-31',
            'total_amount' => '100000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'PENDING_MATCH',
        ]);

        Bir2307Certificate::create([
            'certificate_number' => '2307-2026-000001',
            'purchase_bill_id'   => $bill->id,
            'vendor_id'          => $vendor->id,
            'period_from'        => '2026-07-01',
            'period_to'          => '2026-09-30',
            'payee_name'         => $vendor->name,
            'payee_tin'          => $vendor->tin,
            'atc_code'           => 'WI158',
            'tax_base_amount'    => '100000.0000',
            'tax_rate'           => '0.0100',
            'tax_withheld'       => '1000.0000',
            'form_status'        => 'GENERATED',
        ]);

        Bir2307Certificate::create([
            'certificate_number' => '2307-2026-000002',
            'purchase_bill_id'   => $bill->id,
            'vendor_id'          => $vendor->id,
            'period_from'        => '2026-08-01',
            'period_to'          => '2026-08-31',
            'payee_name'         => 'Dr. Jose Rizal',
            'payee_tin'          => '998-776-554-000',
            'atc_code'           => 'WI010',
            'tax_base_amount'    => '50000.0000',
            'tax_rate'           => '0.1000',
            'tax_withheld'       => '5000.0000',
            'form_status'        => 'GENERATED',
        ]);

        $summary = $this->taxScheduleService->getBir1601EQSummary('2026-07-01', '2026-09-30');

        $this->assertSame(2, $summary['total_forms']);
        $this->assertSame('150000.0000', $summary['total_tax_base']);
        $this->assertSame('6000.0000', $summary['total_withheld']);
        $this->assertCount(2, $summary['schedules']);
    }

    /** @test */
    public function test_bir_tax_schedule_service_calculates_bir_1601_c_summary_from_payroll_runs(): void
    {
        PayrollRun::create([
            'payroll_run_number'          => 'PR-2026-08A',
            'cutoff_start'                => '2026-08-01',
            'cutoff_end'                  => '2026-08-15',
            'payout_date'                 => '2026-08-15',
            'employee_count'              => 45,
            'total_gross_pay'             => '600000.0000',
            'total_sss_employee'          => '20000.0000',
            'total_sss_employer'          => '40000.0000',
            'total_philhealth_employee'   => '15000.0000',
            'total_philhealth_employer'   => '15000.0000',
            'total_pagibig_employee'      => '5000.0000',
            'total_pagibig_employer'      => '5000.0000',
            'total_withholding_tax_1601c' => '42000.0000',
            'total_statutory_deductions'  => '82000.0000',
            'total_net_pay'               => '518000.0000',
            'status'                      => 'DISBURSED',
        ]);

        $summary = $this->taxScheduleService->getBir1601CSummary('2026', '08');

        $this->assertSame(45, $summary['employee_count']);
        $this->assertSame('600000.0000', $summary['gross_compensation']);
        $this->assertSame('40000.0000', $summary['statutory_exemptions']); // 20000 + 15000 + 5000
        $this->assertSame('560000.0000', $summary['taxable_compensation']); // 600000 - 40000
        $this->assertSame('42000.0000', $summary['tax_withheld']);
    }

    /** @test */
    public function test_bir_tax_schedule_service_calculates_bir_vat_summary_from_official_receipts(): void
    {
        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-9001',
            'full_name'         => 'Walk-in Retail Client',
            'admission_type'    => 'Outpatient',
            'current_balance'   => '0.0000',
            'status'            => 'Active',
        ]);

        $payment = Payment::create([
            'payment_reference'  => 'PAY-2026-0001',
            'patient_account_id' => $patient->id,
            'payment_date'       => '2026-08-10',
            'amount'             => '129600.0000',
            'payment_method'     => 'CASH',
            'payment_type'       => 'PATIENT_COPAY',
        ]);

        OfficialReceipt::create([
            'or_number'              => 'OR-2026-00001',
            'payment_id'             => $payment->id,
            'patient_account_id'     => $patient->id,
            'or_date'                => '2026-08-10',
            'payor_name'             => 'Walk-in Retail Pharmacy Client',
            'payor_tin'              => '000-000-000-000',
            'vatable_sales'          => '80000.0000',
            'vat_exempt_sales'       => '40000.0000',
            'zero_rated_sales'       => '0.0000',
            'vat_amount'             => '9600.0000',
            'total_amount_collected' => '129600.0000',
            'status'                 => 'VALID',
        ]);

        $summary = $this->taxScheduleService->getBirVatSummary('2026-07-01', '2026-09-30');

        $this->assertSame(1, $summary['receipts_count']);
        $this->assertSame('80000.0000', $summary['vatable_sales']);
        $this->assertSame('40000.0000', $summary['vat_exempt_sales']);
        $this->assertSame('9600.0000', $summary['output_vat_12']);
        $this->assertSame('129600.0000', $summary['total_collections']);
    }

    /** @test */
    public function test_withholding_tax_view_renders_bir_2307_certificates_with_correct_attributes(): void
    {
        $vendor = Vendor::create([
            'vendor_code'      => 'VND-002',
            'name'             => 'Global Med Devices',
            'tin'              => '222-333-444-000',
            'tax_type'         => 'VAT_REGISTERED',
            'default_ewt_rate' => 0.0200,
            'default_atc_code' => 'WI160',
            'status'           => 'ACTIVE',
        ]);

        $bill = PurchaseBill::create([
            'bill_number'  => 'PB-2026-002',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-08-05',
            'due_date'     => '2026-09-05',
            'total_amount' => '250000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'MATCHED',
        ]);

        Bir2307Certificate::create([
            'certificate_number' => '2307-2026-000099',
            'purchase_bill_id'   => $bill->id,
            'vendor_id'          => $vendor->id,
            'period_from'        => '2026-08-01',
            'period_to'          => '2026-08-31',
            'payee_name'         => $vendor->name,
            'payee_tin'          => $vendor->tin,
            'atc_code'           => 'WI160',
            'tax_base_amount'    => '250000.0000',
            'tax_rate'           => '0.0200',
            'tax_withheld'       => '5000.0000',
            'form_status'        => 'GENERATED',
        ]);

        $this->actingAs($this->accountant);

        $response = $this->get(route('tax.withholding-tax'));
        $response->assertStatus(200);
        $response->assertSee('2307-2026-000099');
        $response->assertSee('Global Med Devices');
        $response->assertSee('222-333-444-000');
        $response->assertSee('WI160');
        $response->assertSee('250,000.00');
        $response->assertSee('5,000.00');
    }

    /** @test */
    public function test_tax_returns_workstation_renders_live_statutory_schedules(): void
    {
        $this->actingAs($this->accountant);

        $response = $this->get(route('tax.tax-returns'));
        $response->assertStatus(200);
        $response->assertSee('Live Statutory Tax Computations');
        $response->assertSee('BIR Form 1601-EQ (EWT)');
        $response->assertSee('BIR Form 1601-C (Payroll)');
        $response->assertSee('BIR Form 2550Q (VAT)');
    }

    /** @test */
    public function test_authorized_finance_manager_can_file_statutory_tax_return(): void
    {
        $this->actingAs($this->manager);

        $payload = [
            'form_type'      => '2550Q',
            'period_covered' => 'Q3 2026 (Jul - Sep)',
            'filing_date'    => '2026-10-25',
            'tax_due'        => '185000.00',
        ];

        $response = $this->post(route('tax.tax-returns.store'), $payload);
        $response->assertRedirect(route('tax.tax-returns'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tax_returns', [
            'form_type'      => '2550Q',
            'period_covered' => 'Q3 2026 (Jul - Sep)',
            'tax_due'        => '185000.0000',
            'status'         => 'FILED',
        ]);

        $taxReturn = TaxReturn::where('form_type', '2550Q')->firstOrFail();
        $this->assertStringStartsWith('TR-2026-', $taxReturn->return_number);

        $this->assertDatabaseHas('cas_audit_trails', [
            'auditable_type' => TaxReturn::class,
            'auditable_id'   => $taxReturn->id,
            'action'         => 'POST',
        ]);
    }

    /** @test */
    public function test_authorized_finance_manager_can_mark_tax_return_as_paid(): void
    {
        $taxReturn = TaxReturn::create([
            'return_number'  => 'TR-2026-00010',
            'form_type'      => '1601-EQ',
            'period_covered' => 'Q2 2026',
            'tax_due'        => '95000.0000',
            'status'         => 'FILED',
            'filing_date'    => '2026-07-25',
        ]);

        $this->actingAs($this->manager);

        $response = $this->post(route('tax.tax-returns.pay', $taxReturn->id));
        $response->assertRedirect(route('tax.tax-returns'));
        $response->assertSessionHas('success');

        $this->assertSame('PAID', $taxReturn->fresh()->status);

        $this->assertDatabaseHas('cas_audit_trails', [
            'auditable_type' => TaxReturn::class,
            'auditable_id'   => $taxReturn->id,
            'action'         => 'UPDATE',
        ]);
    }

    /** @test */
    public function test_cannot_mark_already_paid_tax_return_as_paid_again(): void
    {
        $taxReturn = TaxReturn::create([
            'return_number'  => 'TR-2026-00011',
            'form_type'      => '1601-EQ',
            'period_covered' => 'Q2 2026',
            'tax_due'        => '95000.0000',
            'status'         => 'PAID',
            'filing_date'    => '2026-07-25',
        ]);

        $this->actingAs($this->manager);

        $response = $this->post(route('tax.tax-returns.pay', $taxReturn->id));
        $response->assertRedirect(route('tax.tax-returns'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_authorized_finance_manager_can_store_and_toggle_tax_rules(): void
    {
        $this->actingAs($this->manager);

        $payload = [
            'tax_code' => 'WI030',
            'name'     => 'EWT on Laboratory Services (5%)',
            'atc_code' => 'WI030',
            'category' => 'WITHHOLDING_TAX',
            'cat_type' => 'EXPANDED',
            'rate'     => '5.0000',
            'scope'    => 'Third-party clinical testing laboratories',
        ];

        $response = $this->post(route('tax.tax-rules.store'), $payload);
        $response->assertRedirect(route('tax.tax-config'));
        $response->assertSessionHas('success');

        $rule = TaxRule::where('tax_code', 'WI030')->firstOrFail();
        $this->assertSame('Active', $rule->status);
        $this->assertSame('5.0000', (string) $rule->rate);

        // Toggle status to Inactive
        $toggleResponse = $this->post(route('tax.tax-rules.toggle', $rule->id));
        $toggleResponse->assertRedirect(route('tax.tax-config'));
        $this->assertSame('Inactive', $rule->fresh()->status);
    }

    /** @test */
    public function test_tax_exemptions_and_audit_trail_views_render_properly(): void
    {
        TaxRule::create([
            'tax_code' => 'VAT-EXEMPT-MED',
            'name'     => 'CREATE Act Medicine Relief',
            'atc_code' => 'VAT-EXEMPT',
            'category' => 'VAT',
            'cat_type' => 'EXEMPT',
            'rate'     => '0.0000',
            'status'   => 'Active',
        ]);

        $this->actingAs($this->accountant);

        $exemptionResp = $this->get(route('tax.tax-exemptions'));
        $exemptionResp->assertStatus(200);
        $exemptionResp->assertSee('CREATE Act');
        $exemptionResp->assertSee('Expanded Senior Citizens Act');

        $auditResp = $this->get(route('tax.tax-audit'));
        $auditResp->assertStatus(200);
        $auditResp->assertSee('Audit Timestamp');
    }

    /** @test */
    public function test_unauthorized_roles_blocked_from_tax_management_endpoints(): void
    {
        $this->actingAs($this->cashier);

        // Cashier attempting to view tax config -> 403 Forbidden
        $viewResp = $this->get(route('tax.tax-config'));
        $viewResp->assertStatus(403);

        // Cashier attempting to file tax return -> 403 Forbidden
        $fileResp = $this->post(route('tax.tax-returns.store'), [
            'form_type'      => '2550Q',
            'period_covered' => 'Q3 2026',
            'filing_date'    => '2026-10-25',
            'tax_due'        => '10000.00',
        ]);
        $fileResp->assertStatus(403);

        // Staff Accountant can view but cannot file tax return (Maker-Checker SoD)
        $this->actingAs($this->accountant);

        $accountantFileResp = $this->post(route('tax.tax-returns.store'), [
            'form_type'      => '2550Q',
            'period_covered' => 'Q3 2026',
            'filing_date'    => '2026-10-25',
            'tax_due'        => '10000.00',
        ]);
        $accountantFileResp->assertStatus(403);
    }
}
