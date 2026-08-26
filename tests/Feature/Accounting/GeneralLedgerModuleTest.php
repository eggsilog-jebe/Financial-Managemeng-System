<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\CasAuditTrail;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GeneralLedgerModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $cfo;
    private User $accountant;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'Dr. CFO Officer', 'email' => 'cfo@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Manager One', 'email' => 'manager@hospital.local']);
        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Accountant Staff', 'email' => 'accountant@hospital.local']);
    }

    /** @test */
    public function test_chart_of_accounts_index_and_creation(): void
    {
        $this->actingAs($this->accountant);

        // 1. View COA Page
        $response = $this->get('/general-ledger/chart-of-accounts');
        $response->assertStatus(200);

        // 2. Create New Account
        $payload = [
            'code'           => '1099',
            'name'           => 'Special Testing Cash Vault',
            'category'       => 'ASSET',
            'normal_balance' => 'DEBIT',
            'department'     => 'Treasury',
            'is_active'      => 1,
        ];

        $postResponse = $this->post('/general-ledger/chart-of-accounts', $payload);
        $postResponse->assertRedirect('/general-ledger/chart-of-accounts');

        $this->assertDatabaseHas('accounts', [
            'code'     => '1099',
            'name'     => 'Special Testing Cash Vault',
            'category' => 'ASSET',
        ]);

        // 3. Verify CAS audit trail was logged
        $this->assertDatabaseHas('cas_audit_trails', [
            'action' => 'INSERT',
        ]);
    }

    /** @test */
    public function test_chart_of_accounts_deactivation_guard_with_transactions(): void
    {
        $this->actingAs($this->cfo);

        $account = Account::create([
            'code'           => '1010',
            'name'           => 'Operating Cash',
            'category'       => 'ASSET',
            'normal_balance' => 'DEBIT',
            'is_active'      => true,
        ]);

        $entry = JournalEntry::create([
            'reference_number' => 'JE-INIT-001',
            'entry_date'       => '2026-01-15',
            'description'      => 'Initial Capital',
            'status'           => 'POSTED',
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id'       => $account->id,
            'debit'            => '10000.0000',
            'credit'           => '0.0000',
        ]);

        // Attempting to deactivate should be rejected
        $toggleResponse = $this->post("/general-ledger/chart-of-accounts/{$account->id}/toggle-status");
        $account->refresh();

        $this->assertTrue($account->is_active);
    }

    /** @test */
    public function test_journal_entry_manual_creation_and_balance_enforcement(): void
    {
        $this->actingAs($this->accountant);

        // Ensure open fiscal period
        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'status'        => 'OPEN',
        ]);

        $cash = Account::create(['code' => '1010', 'name' => 'Cash', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $revenue = Account::create(['code' => '4010', 'name' => 'Hospital Fees', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);

        // 1. Unbalanced Entry should fail validation
        $unbalancedPayload = [
            'entry_date'  => '2026-01-15',
            'description' => 'Unbalanced consultation payment',
            'type'        => 'GENERAL',
            'lines'       => [
                ['account_id' => $cash->id, 'debit' => '500.00', 'credit' => '0.00', 'memo' => 'Debit Cash'],
                ['account_id' => $revenue->id, 'debit' => '0.00', 'credit' => '450.00', 'memo' => 'Credit Rev (Unbalanced)'],
            ],
        ];

        $failResponse = $this->post('/general-ledger/journal-entries', $unbalancedPayload);
        $failResponse->assertSessionHasErrors('lines');

        // 2. Balanced Entry should succeed
        $balancedPayload = [
            'entry_date'  => '2026-01-15',
            'description' => 'Consultation Fee Collection',
            'type'        => 'GENERAL',
            'auto_post'   => 1,
            'lines'       => [
                ['account_id' => $cash->id, 'debit' => '500.00', 'credit' => '0.00', 'memo' => 'Cash received'],
                ['account_id' => $revenue->id, 'debit' => '0.00', 'credit' => '500.00', 'memo' => 'Consultation Revenue'],
            ],
        ];

        $successResponse = $this->post('/general-ledger/journal-entries', $balancedPayload);
        $successResponse->assertRedirect('/general-ledger/journal-entries');

        $this->assertDatabaseHas('journal_entries', [
            'description' => 'Consultation Fee Collection',
            'status'      => 'POSTED',
        ]);
    }

    /** @test */
    public function test_journal_entry_posting_and_reversal_with_audit_trail(): void
    {
        $this->actingAs($this->manager);

        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'status'        => 'OPEN',
        ]);

        $cash = Account::create(['code' => '1010', 'name' => 'Cash', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $supplies = Account::create(['code' => '5010', 'name' => 'Medical Supplies', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);

        // Create Draft Entry
        $draftEntry = JournalEntry::create([
            'reference_number' => 'JE-DRAFT-001',
            'entry_date'       => '2026-01-10',
            'description'      => 'Pending Supply Purchase',
            'type'             => 'GENERAL',
            'status'           => 'DRAFT',
        ]);

        JournalEntryLine::create(['journal_entry_id' => $draftEntry->id, 'account_id' => $supplies->id, 'debit' => '2500.00', 'credit' => '0.00']);
        JournalEntryLine::create(['journal_entry_id' => $draftEntry->id, 'account_id' => $cash->id, 'debit' => '0.00', 'credit' => '2500.00']);

        // 1. Post Action
        $postResponse = $this->post("/general-ledger/journal-entries/{$draftEntry->id}/post");
        $postResponse->assertRedirect('/general-ledger/journal-entries');

        $draftEntry->refresh();
        $this->assertEquals('POSTED', $draftEntry->status);

        // 2. Reverse Action
        $reverseResponse = $this->post("/general-ledger/journal-entries/{$draftEntry->id}/reverse", [
            'reason' => 'Duplicate purchase invoice recorded in error',
        ]);
        $reverseResponse->assertRedirect('/general-ledger/journal-entries');

        $draftEntry->refresh();
        $this->assertEquals('REVERSED', $draftEntry->status);
        $this->assertNotNull($draftEntry->reversed_by_entry_id);

        $reversalEntry = JournalEntry::find($draftEntry->reversed_by_entry_id);
        $this->assertNotNull($reversalEntry);
        $this->assertEquals('POSTED', $reversalEntry->status);

        // Check reversed lines (debits and credits flipped)
        $revCashLine = $reversalEntry->lines()->where('account_id', $cash->id)->first();
        $this->assertEquals('2500.0000', $revCashLine->debit);
    }

    /** @test */
    public function test_journal_entry_period_lock_guard(): void
    {
        $this->actingAs($this->accountant);

        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-01-31',
            'status'        => 'LOCKED', // Period is locked
        ]);

        $cash = Account::create(['code' => '1010', 'name' => 'Cash', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $revenue = Account::create(['code' => '4010', 'name' => 'Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);

        $payload = [
            'entry_date'  => '2026-01-15',
            'description' => 'Should fail due to period lock',
            'type'        => 'GENERAL',
            'lines'       => [
                ['account_id' => $cash->id, 'debit' => '1000.00', 'credit' => '0.00'],
                ['account_id' => $revenue->id, 'debit' => '0.00', 'credit' => '1000.00'],
            ],
        ];

        $response = $this->post('/general-ledger/journal-entries', $payload);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function test_ledger_books_running_balance_and_export(): void
    {
        $this->actingAs($this->cfo);

        $cash = Account::create(['code' => '1010', 'name' => 'Cash on Hand', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $equity = Account::create(['code' => '3010', 'name' => 'Owner Capital', 'category' => 'EQUITY', 'normal_balance' => 'CREDIT']);
        $revenue = Account::create(['code' => '4010', 'name' => 'Pharmacy Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);

        // Transaction 1: Jan 10 Capital +10,000
        $je1 = JournalEntry::create(['reference_number' => 'JE-001', 'entry_date' => '2026-01-10', 'description' => 'Capital', 'status' => 'POSTED']);
        JournalEntryLine::create(['journal_entry_id' => $je1->id, 'account_id' => $cash->id, 'debit' => '10000.0000', 'credit' => '0.0000']);
        JournalEntryLine::create(['journal_entry_id' => $je1->id, 'account_id' => $equity->id, 'debit' => '0.0000', 'credit' => '10000.0000']);

        // Transaction 2: Jan 20 Pharmacy sale +2,500
        $je2 = JournalEntry::create(['reference_number' => 'JE-002', 'entry_date' => '2026-01-20', 'description' => 'Pharmacy Sale', 'status' => 'POSTED']);
        JournalEntryLine::create(['journal_entry_id' => $je2->id, 'account_id' => $cash->id, 'debit' => '2500.0000', 'credit' => '0.0000']);
        JournalEntryLine::create(['journal_entry_id' => $je2->id, 'account_id' => $revenue->id, 'debit' => '0.0000', 'credit' => '2500.0000']);

        // 1. View Ledger Book
        $response = $this->get("/general-ledger/ledger-books?account_id={$cash->id}");
        $response->assertStatus(200);
        $response->assertSee('12,500.00');

        // 2. Export Statement CSV
        $exportResponse = $this->get("/general-ledger/ledger-books/export?account_id={$cash->id}");
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_trial_balance_verification_and_export(): void
    {
        $this->actingAs($this->accountant);

        $cash = Account::create(['code' => '1010', 'name' => 'Cash', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $revenue = Account::create(['code' => '4010', 'name' => 'Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);

        $je = JournalEntry::create(['reference_number' => 'JE-TB-001', 'entry_date' => '2026-01-15', 'description' => 'Consultation', 'status' => 'POSTED']);
        JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $cash->id, 'debit' => '5000.0000', 'credit' => '0.0000']);
        JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $revenue->id, 'debit' => '0.0000', 'credit' => '5000.0000']);

        // 1. View Trial Balance
        $response = $this->get('/general-ledger/trial-balance');
        $response->assertStatus(200);
        $response->assertSee('TRIAL BALANCE IS BALANCED');
        $response->assertSee('5,000.00');

        // 2. Export Trial Balance CSV
        $exportResponse = $this->get('/general-ledger/trial-balance/export');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_fiscal_period_initialization_soft_lock_and_cfo_hard_close(): void
    {
        $this->actingAs($this->cfo);

        // 1. Initialize FY 2027
        $initResponse = $this->post('/general-ledger/period-end-closing/initialize', ['fiscal_year' => '2027']);
        $initResponse->assertRedirect('/general-ledger/period-end-closing?fiscal_year=2027');

        $this->assertDatabaseCount('fiscal_periods', 12);
        $jan2027 = FiscalPeriod::where('period_code', '2027-M01')->firstOrFail();
        $this->assertEquals('OPEN', $jan2027->status);

        // 2. Soft Lock Jan 2027
        $lockResponse = $this->post("/general-ledger/period-end-closing/{$jan2027->id}/lock");
        $lockResponse->assertRedirect('/general-ledger/period-end-closing?fiscal_year=2027');

        $jan2027->refresh();
        $this->assertTrue($jan2027->isLocked());

        // Setup some revenues and expenses in Jan 2027
        $revAcc = Account::create(['code' => '4001', 'name' => 'Lab Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);
        $expAcc = Account::create(['code' => '5001', 'name' => 'Reagent Expenses', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
        $retainedAcc = Account::create(['code' => '3020', 'name' => 'Retained Earnings', 'category' => 'EQUITY', 'normal_balance' => 'CREDIT']);

        $je = JournalEntry::create(['reference_number' => 'JE-JAN-001', 'entry_date' => '2027-01-15', 'description' => 'Lab fee', 'status' => 'POSTED']);
        JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $revAcc->id, 'debit' => '0.0000', 'credit' => '8000.0000']);
        JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $expAcc->id, 'debit' => '3000.0000', 'credit' => '0.0000']);
        $cashAcc = Account::create(['code' => '1001', 'name' => 'Cash', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        JournalEntryLine::create(['journal_entry_id' => $je->id, 'account_id' => $cashAcc->id, 'debit' => '5000.0000', 'credit' => '0.0000']);

        // 3. CFO Hard Close
        $closeResponse = $this->post("/general-ledger/period-end-closing/{$jan2027->id}/close");
        $closeResponse->assertRedirect('/general-ledger/period-end-closing?fiscal_year=2027');

        $jan2027->refresh();
        $this->assertTrue($jan2027->isClosed());
        $this->assertNotNull($jan2027->closing_journal_entry_id);

        $closingJE = $jan2027->closingJournalEntry;
        $this->assertNotNull($closingJE);
        $this->assertEquals('CLOSING', $closingJE->type);

        // Check that Net Income (8000 - 3000 = 5000) was credited to Retained Earnings
        $reLine = $closingJE->lines()->where('account_id', $retainedAcc->id)->first();
        $this->assertNotNull($reLine);
        $this->assertEquals('5000.0000', $reLine->credit);
    }
}
