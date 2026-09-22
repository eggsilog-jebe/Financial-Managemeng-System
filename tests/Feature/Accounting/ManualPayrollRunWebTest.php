<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ManualPayrollRunWebTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private User $manager;
    private User $cashier;
    private BankAccount $operatingBank;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ChartOfAccountsSeeder::class);

        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Eduardo Mendoza', 'email' => 'accountant@hospital.local']);
        $this->manager    = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Roberto Cruz', 'email' => 'manager@hospital.local']);
        $this->cashier    = User::factory()->create(['role' => 'Cashier', 'name' => 'Maria Clara', 'email' => 'cashier@hospital.local']);

        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);

        $bankGl = Account::where('code', '1020')->firstOrFail();
        $this->operatingBank = BankAccount::create([
            'name'            => 'Operating Treasury Account',
            'bank_name'       => 'Metrobank Pasig Branch',
            'account_number'  => 'MB-9920-1122-33',
            'gl_code'         => '1020',
            'gl_account_id'   => $bankGl->id,
            'purpose'         => 'Hospital Daily Operations & Payroll',
            'currency'        => 'PHP',
            'opening_balance' => '1000000.0000',
            'balance'         => '1000000.0000',
            'minimum_balance' => '100000.0000',
            'status'          => 'Active',
            'is_active'       => true,
        ]);
    }

    /** @test */
    public function test_payment_requests_page_displays_encode_payroll_button_and_modal(): void
    {
        $this->actingAs($this->accountant);

        $response = $this->get(route('disbursement.payment-requests'));

        $response->assertStatus(200);
        $response->assertSee('Encode Payroll Cutoff Run');
        $response->assertSee('id="encodePayrollModal"', false);
        $response->assertSee('name="cutoff_start"', false);
        $response->assertSee('name="cutoff_end"', false);
        $response->assertSee('name="payout_date"', false);
        $response->assertSee('name="disbursement_bank_account_id"', false);
        $response->assertSee('id="payrollEmployeesTable"', false);
    }

    /** @test */
    public function test_accountant_can_manually_submit_payroll_cutoff_run(): void
    {
        $this->actingAs($this->accountant);

        $payload = [
            'cutoff_start'                 => '2026-01-01',
            'cutoff_end'                   => '2026-01-15',
            'payout_date'                  => '2026-01-15',
            'disbursement_bank_account_id' => $this->operatingBank->id,
            'employees'                    => [
                [
                    'employee_id_number'  => 'EMP-2026-001',
                    'employee_name'       => 'Dr. Roberto Mendoza, MD',
                    'department'          => 'Medical / Physicians',
                    'basic_salary'        => 65000.00,
                    'overtime_pay'        => 5000.00,
                    'allowances'          => 10000.00,
                    'bank_account_number' => 'BDO-001293847',
                ],
                [
                    'employee_id_number'  => 'EMP-2026-002',
                    'employee_name'       => 'Ma. Elena Reyes, RN',
                    'department'          => 'Nursing',
                    'basic_salary'        => 32000.00,
                    'overtime_pay'        => 3500.00,
                    'allowances'          => 2000.00,
                    'bank_account_number' => 'BPI-992384712',
                ],
            ],
        ];

        $response = $this->post(route('disbursement.payroll.store'), $payload);

        $response->assertRedirect(route('disbursement.payment-requests'));
        $response->assertSessionHas('success');

        // Verify PayrollRun record in DB
        $payrollRun = PayrollRun::where('status', 'DISBURSED')->first();
        $this->assertNotNull($payrollRun);
        $this->assertEquals(2, $payrollRun->employee_count);
        $this->assertGreaterThan(0, (float) $payrollRun->total_gross_pay);
        $this->assertGreaterThan(0, (float) $payrollRun->total_net_pay);

        // Verify Individual Payroll Items
        $items = PayrollItem::where('payroll_run_id', $payrollRun->id)->get();
        $this->assertCount(2, $items);
        $this->assertEquals('Dr. Roberto Mendoza, MD', $items[0]->employee_name);
        $this->assertGreaterThan(0, (float) $items[0]->sss_employee_share);
        $this->assertGreaterThan(0, (float) $items[0]->philhealth_employee_share);
        $this->assertGreaterThan(0, (float) $items[0]->withholding_tax);

        // Verify Disbursement Voucher creation
        $voucher = DisbursementVoucher::where('payroll_run_id', $payrollRun->id)->first();
        $this->assertNotNull($voucher);
        $this->assertEquals('RELEASED', $voucher->status);
        $this->assertEquals('PESONET_EFT', $voucher->payment_method);
        $this->assertEquals($payrollRun->total_net_pay, $voucher->net_disbursed_amount);

        // Verify Double-Entry GL Journal Entry created & balanced
        $je = JournalEntry::where('reference_number', $payrollRun->payroll_run_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);
        $this->assertTrue(bccomp((string) $je->total_debit, (string) $je->total_credit, 4) === 0);

        // Verify Bank Account Balance was debited/decreased
        $freshBank = $this->operatingBank->fresh();
        $expectedBalance = bcsub('1000000.0000', (string) $payrollRun->total_net_pay, 4);
        $this->assertTrue(bccomp((string) $freshBank->balance, $expectedBalance, 4) === 0);
    }

    /** @test */
    public function test_insufficient_bank_balance_returns_error_flash(): void
    {
        $this->actingAs($this->accountant);

        // Set bank balance to 100 pesos
        $this->operatingBank->update(['balance' => '100.0000']);

        $payload = [
            'cutoff_start'                 => '2026-01-01',
            'cutoff_end'                   => '2026-01-15',
            'payout_date'                  => '2026-01-15',
            'disbursement_bank_account_id' => $this->operatingBank->id,
            'employees'                    => [
                [
                    'employee_id_number'  => 'EMP-001',
                    'employee_name'       => 'Dr. Santos',
                    'department'          => 'Medical',
                    'basic_salary'        => 50000.00,
                ],
            ],
        ];

        $response = $this->from(route('disbursement.payment-requests'))
            ->post(route('disbursement.payroll.store'), $payload);

        $response->assertRedirect(route('disbursement.payment-requests'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_unauthorized_roles_cannot_post_payroll_runs(): void
    {
        // Cashier cannot post payroll runs
        $this->actingAs($this->cashier);

        $response = $this->post(route('disbursement.payroll.store'), [
            'cutoff_start'                 => '2026-01-01',
            'cutoff_end'                   => '2026-01-15',
            'payout_date'                  => '2026-01-15',
            'disbursement_bank_account_id' => $this->operatingBank->id,
            'employees'                    => [],
        ]);

        $response->assertStatus(403);
    }
}
