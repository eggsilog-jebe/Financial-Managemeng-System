<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Bir2307Certificate;
use App\Models\PurchaseBill;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportsExportTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ChartOfAccountsSeeder::class);

        $this->accountant = User::factory()->create([
            'name'  => 'Eduardo Mendoza (Accountant)',
            'role'  => 'StaffAccountant',
            'email' => 'accountant@hospital.local',
        ]);
    }

    /** @test */
    public function test_trial_balance_csv_exports_successfully_and_is_balanced(): void
    {
        $this->actingAs($this->accountant);

        $response = $this->get('/accounting/export/trial-balance-csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('General Ledger Trial Balance Report', $response->streamedContent());
        $this->assertStringContainsString('GRAND TOTALS', $response->streamedContent());
        $this->assertStringContainsString('BALANCED', $response->streamedContent());
    }

    /** @test */
    public function test_general_ledger_book_csv_exports_successfully(): void
    {
        $this->actingAs($this->accountant);

        $response = $this->get('/accounting/export/general-ledger-csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('General Ledger Book & Transaction Register', $response->streamedContent());
    }

    /** @test */
    public function test_bir_2307_certificate_printable_view_renders_valid_tax_data(): void
    {
        $this->actingAs($this->accountant);

        $vendor = Vendor::create([
            'code'   => 'VEND-990',
            'name'   => 'B. Braun Medical Supplies',
            'tin'    => '111-222-333-000',
            'status' => 'Active',
        ]);

        $bill = PurchaseBill::create([
            'bill_number'   => 'PB-2026-0091',
            'vendor_id'     => $vendor->id,
            'bill_date'     => now()->toDateString(),
            'due_date'      => now()->addDays(30)->toDateString(),
            'total_amount'  => 100000.00,
            'paid_amount'   => 0.00,
            'status'        => 'UNPAID',
        ]);

        $cert = Bir2307Certificate::create([
            'certificate_number' => '2307-2026-TEST-01',
            'purchase_bill_id'   => $bill->id,
            'vendor_id'          => $vendor->id,
            'period_from'        => now()->startOfMonth()->toDateString(),
            'period_to'          => now()->endOfMonth()->toDateString(),
            'payee_name'         => $vendor->name,
            'payee_tin'          => $vendor->tin,
            'atc_code'           => 'WI158',
            'tax_base_amount'    => 100000.00,
            'tax_rate'           => 0.01,
            'tax_withheld'       => 1000.00,
            'form_status'        => 'GENERATED',
        ]);

        $response = $this->get("/accounting/print/bir-2307/{$cert->id}");

        $response->assertStatus(200);
        $response->assertSee('BIR Form No. 2307');
        $response->assertSee('B. Braun Medical Supplies');
        $response->assertSee('WI158');
        $response->assertSee('1,000.00');
    }
}
