<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\DTOs\Accounting\JournalEntryData;
use App\Exceptions\Accounting\UnbalancedJournalEntryException;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndFinancialFlowTest extends TestCase
{
    use RefreshDatabase;

    private JournalEntryService $glService;
    private User $accountant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->glService = app(JournalEntryService::class);
        $this->accountant = User::factory()->create();
    }

    /** @test */
    public function test_it_rejects_unbalanced_double_entry_transactions(): void
    {
        $this->expectException(UnbalancedJournalEntryException::class);

        $cashAccount = Account::factory()->create(['code' => '1010', 'type' => 'ASSET']);
        $revAccount = Account::factory()->create(['code' => '4010', 'type' => 'REVENUE']);

        $data = new JournalEntryData(
            entryDate: now()->toDateString(),
            description: 'Unbalanced Attempt',
            lines: [
                ['account_id' => $cashAccount->id, 'debit' => '100.0000', 'credit' => '0.0000'],
                ['account_id' => $revAccount->id, 'debit' => '0.0000', 'credit' => '95.0000'], // 5.00 discrepancy
            ],
            userId: $this->accountant->id
        );

        $this->glService->createAndPostEntry($data);
    }

    /** @test */
    public function test_it_executes_a_full_philippine_patient_billing_and_cashier_flow(): void
    {
        // 1. Setup COA Accounts
        $arPatient = Account::factory()->create(['code' => '1120', 'name' => 'AR - Patient', 'type' => 'ASSET']);
        $arPhilHealth = Account::factory()->create(['code' => '1130', 'name' => 'AR - PhilHealth', 'type' => 'ASSET']);
        $seniorDiscount = Account::factory()->create(['code' => '4900', 'name' => 'Senior Citizen Discount', 'type' => 'EXPENSE']);
        $revenue = Account::factory()->create(['code' => '4000', 'name' => 'Hospital Inpatient Revenue', 'type' => 'REVENUE']);
        $cashDrawer = Account::factory()->create(['code' => '1011', 'name' => 'Cashier Undeposited Collections', 'type' => 'ASSET']);
        $bankAccount = Account::factory()->create(['code' => '1020', 'name' => 'Cash in Bank - Operating', 'type' => 'ASSET']);

        // 2. Simulate Inpatient Bill (Total: ₱10,000 | PhilHealth: ₱4,000 | Senior 20% on remaining: ₱1,200 | Copay: ₱4,800)
        $billingEntry = $this->glService->createAndPostEntry(new JournalEntryData(
            entryDate: now()->toDateString(),
            description: 'Discharge Bill #INV-2026-001 with PhilHealth & Senior Discount',
            referenceNumber: 'INV-2026-001',
            lines: [
                ['account_id' => $arPhilHealth->id, 'debit' => '4000.0000', 'credit' => '0.0000', 'memo' => 'Case Rate ACR-01'],
                ['account_id' => $arPatient->id, 'debit' => '4800.0000', 'credit' => '0.0000', 'memo' => 'Patient Copay'],
                ['account_id' => $seniorDiscount->id, 'debit' => '1200.0000', 'credit' => '0.0000', 'memo' => 'RA 9994 20% Discount'],
                ['account_id' => $revenue->id, 'debit' => '0.0000', 'credit' => '10000.0000', 'memo' => 'Inpatient Care Gross'],
            ],
            userId: $this->accountant->id
        ));

        $this->assertEquals('POSTED', $billingEntry->status);
        $this->assertCount(4, $billingEntry->lines);

        // 3. Cashier Payment (₱4,800 settled via QR Ph / Cash)
        $collectionEntry = $this->glService->createAndPostEntry(new JournalEntryData(
            entryDate: now()->toDateString(),
            description: 'Cashier Official Receipt #OR-2026-0001',
            referenceNumber: 'OR-2026-0001',
            lines: [
                ['account_id' => $cashDrawer->id, 'debit' => '4800.0000', 'credit' => '0.0000', 'memo' => 'Payment received'],
                ['account_id' => $arPatient->id, 'debit' => '0.0000', 'credit' => '4800.0000', 'memo' => 'AR Cleared'],
            ],
            userId: $this->accountant->id
        ));

        $this->assertEquals('POSTED', $collectionEntry->status);

        // 4. Shift End Bank Deposit
        $depositEntry = $this->glService->createAndPostEntry(new JournalEntryData(
            entryDate: now()->toDateString(),
            description: 'Bank Deposit Slip #DEP-9901',
            referenceNumber: 'DEP-9901',
            lines: [
                ['account_id' => $bankAccount->id, 'debit' => '4800.0000', 'credit' => '0.0000', 'memo' => 'Deposited to Operating Account'],
                ['account_id' => $cashDrawer->id, 'debit' => '0.0000', 'credit' => '4800.0000', 'memo' => 'Clear Cash Drawer'],
            ],
            userId: $this->accountant->id
        ));

        $this->assertEquals('POSTED', $depositEntry->status);
    }

    /** @test */
    public function test_it_reverses_a_posted_entry_without_modifying_original_records(): void
    {
        $cash = Account::factory()->create(['code' => '1010', 'type' => 'ASSET']);
        $ap = Account::factory()->create(['code' => '2010', 'type' => 'LIABILITY']);

        $original = $this->glService->createAndPostEntry(new JournalEntryData(
            entryDate: now()->toDateString(),
            description: 'Supplier Advance Payment',
            referenceNumber: 'ADV-01',
            lines: [
                ['account_id' => $ap->id, 'debit' => '5000.0000', 'credit' => '0.0000'],
                ['account_id' => $cash->id, 'debit' => '0.0000', 'credit' => '5000.0000'],
            ],
            userId: $this->accountant->id
        ));

        $reversal = $this->glService->reverseEntry($original, 'Erroneous Vendor Tagging', $this->accountant->id);

        $this->assertEquals('REVERSED', $original->fresh()->status);
        $this->assertEquals($reversal->id, $original->fresh()->reversed_by_entry_id);
        $this->assertEquals('POSTED', $reversal->status);
    }
}
