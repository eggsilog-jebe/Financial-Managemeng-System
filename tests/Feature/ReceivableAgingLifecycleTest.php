<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\Accounting\PatientInvoiceCreateData;
use App\DTOs\Accounting\PosCollectionData;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PatientAccount;
use App\Services\Accounting\CashierPaymentService;
use App\Services\Accounting\InvoiceBillingService;
use App\Services\Accounting\ReceivableAgingService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReceivableAgingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceBillingService $billingService;
    private CashierPaymentService $cashierPaymentService;
    private ReceivableAgingService $agingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ChartOfAccountsSeeder::class);

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $this->billingService = $this->app->make(InvoiceBillingService::class);
        $this->cashierPaymentService = $this->app->make(CashierPaymentService::class);
        $this->agingService = $this->app->make(ReceivableAgingService::class);
    }

    public function test_end_to_end_accounts_receivable_aging_lifecycle(): void
    {
        // Setup Patient Account
        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-TEST1',
            'full_name'         => 'Audit Test Patient',
            'admission_type'    => 'INPATIENT',
            'discount_category' => 'NONE',
            'status'            => 'Active',
        ]);

        // Step 1: Create an invoice backdated 45 days ago for ₱18,400.00 (UNPAID)
        $invoiceDate = now()->subDays(45)->toDateString();
        $createDto = PatientInvoiceCreateData::fromArray([
            'patient_account_id' => $patient->id,
            'invoice_date'       => $invoiceDate,
            'discount_type'      => 'NONE',
            'items'              => [
                [
                    'department'  => 'ROOM_AND_BOARD',
                    'description' => 'Inpatient Room & Board',
                    'quantity'    => 1,
                    'unit_price'  => 18400.00,
                ],
            ],
        ]);

        $invoice = $this->billingService->createPatientInvoice($createDto);

        $this->assertEquals('18400.0000', $invoice->total_amount);
        $this->assertEquals('18400.0000', $invoice->patient_payable);
        $this->assertEquals('0.0000', $invoice->paid_amount);
        $this->assertEquals('UNPAID', $invoice->status);

        // Step 2: Run aging service as-of today and assert balance appears in 31 - 60 Days bucket
        $reportStep2 = $this->agingService->getReceivableAgingReport(now()->toDateString());

        $this->assertEquals('0.0000', $reportStep2['total_current']);
        $this->assertEquals('18400.0000', $reportStep2['total_31_60']);
        $this->assertEquals('0.0000', $reportStep2['total_61_90']);
        $this->assertEquals('0.0000', $reportStep2['total_91_120']);
        $this->assertEquals('0.0000', $reportStep2['total_120_plus']);
        $this->assertEquals('18400.0000', $reportStep2['grand_total']);
        $this->assertCount(1, $reportStep2['debtors']);
        $this->assertEquals('18400.0000', $reportStep2['debtors'][0]['days_31_60']);

        // Step 3: Collect a partial payment of ₱10,000.00. Assert remaining ₱8,400.00 stays in 31 - 60 Days
        $partialCollectionDto = PosCollectionData::fromArray([
            'invoice_id'         => $invoice->id,
            'patient_account_id' => $patient->id,
            'amount'             => '10000.00',
            'payment_method'     => 'CASH',
            'payment_date'       => now()->toDateString(),
        ]);

        $this->cashierPaymentService->collectPayment($partialCollectionDto);

        $invoice->refresh();
        $this->assertEquals('10000.0000', $invoice->paid_amount);
        $this->assertEquals('PARTIAL', $invoice->status);

        $reportStep3 = $this->agingService->getReceivableAgingReport(now()->toDateString());
        $this->assertEquals('8400.0000', $reportStep3['total_31_60']);
        $this->assertEquals('8400.0000', $reportStep3['grand_total']);
        $this->assertCount(1, $reportStep3['debtors']);
        $this->assertEquals('8400.0000', $reportStep3['debtors'][0]['days_31_60']);

        // Step 4: Collect remaining ₱8,400.00. Assert invoice balance becomes ₱0.00 and drops from aging list
        $finalCollectionDto = PosCollectionData::fromArray([
            'invoice_id'         => $invoice->id,
            'patient_account_id' => $patient->id,
            'amount'             => '8400.00',
            'payment_method'     => 'CASH',
            'payment_date'       => now()->toDateString(),
        ]);

        $this->cashierPaymentService->collectPayment($finalCollectionDto);

        $invoice->refresh();
        $this->assertEquals('18400.0000', $invoice->paid_amount);
        $this->assertEquals('SETTLED', $invoice->status);

        $reportStep4 = $this->agingService->getReceivableAgingReport(now()->toDateString());
        $this->assertEquals('0.0000', $reportStep4['grand_total']);
        $this->assertCount(0, $reportStep4['debtors']);

        // Step 5: Assert that all corresponding General Ledger journal entries are strictly balanced (sum(DR) == sum(CR))
        $journalEntries = JournalEntry::with('lines')->get();
        $this->assertNotEmpty($journalEntries);

        foreach ($journalEntries as $entry) {
            $totalDebit = '0.0000';
            $totalCredit = '0.0000';

            foreach ($entry->lines as $line) {
                $totalDebit = bcadd($totalDebit, (string) $line->debit, 4);
                $totalCredit = bcadd($totalCredit, (string) $line->credit, 4);
            }

            $this->assertEquals(
                0,
                bccomp($totalDebit, $totalCredit, 4),
                "Journal Entry {$entry->reference_number} is unbalanced! DR: {$totalDebit}, CR: {$totalCredit}"
            );
        }
    }

    public function test_split_payment_multi_tender_collection_and_gl_posting(): void
    {
        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-SPLIT1',
            'full_name'         => 'Split Payment Test Patient',
            'admission_type'    => 'INPATIENT',
            'discount_category' => 'NONE',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-SPLIT-001',
            'patient_account_id' => $patient->id,
            'invoice_date'       => now()->toDateString(),
            'due_date'           => now()->addDays(30)->toDateString(),
            'total_amount'       => '50000.0000',
            'patient_payable'    => '50000.0000',
            'status'             => 'UNPAID',
        ]);

        $dto = PosCollectionData::fromArray([
            'invoice_id'            => $invoice->id,
            'payment_method'        => 'SPLIT_PAYMENT',
            'amount'                => '50000.00',
            'split_cash_amount'     => '20000.00',
            'split_digital_amount'  => '30000.00',
            'split_digital_channel' => 'CREDIT_CARD',
            'split_digital_ref'     => 'CC-AUTH-998822',
            'payor_name'            => 'Split Payor',
        ]);

        $payment = $this->cashierPaymentService->collectPayment($dto);

        $invoice->refresh();
        $this->assertEquals('SETTLED', $invoice->status);
        $this->assertEquals('0.0000', (string) $invoice->patient_payable);

        // Verify GL entry for split payment
        $glEntry = JournalEntry::where('reference_number', 'JE-COL-' . $payment->payment_reference)->with('lines.account')->first();
        $this->assertNotNull($glEntry);

        // 3 lines: DR 1011 (20k), DR 1002 (30k), CR 1110 (50k)
        $this->assertCount(3, $glEntry->lines);

        $cashLine = $glEntry->lines->firstWhere('account.code', '1011');
        $this->assertNotNull($cashLine);
        $this->assertEquals('20000.0000', (string) $cashLine->debit);

        $digitalLine = $glEntry->lines->firstWhere('account.code', '1002');
        $this->assertNotNull($digitalLine);
        $this->assertEquals('30000.0000', (string) $digitalLine->debit);

        $arLine = $glEntry->lines->firstWhere('account.code', '1110');
        $this->assertNotNull($arLine);
        $this->assertEquals('50000.0000', (string) $arLine->credit);
    }

    public function test_all_receivables_splits_patient_copay_hmo_and_philhealth_debtor_rows(): void
    {
        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-DADA1',
            'full_name'         => 'Dada Pamisa',
            'admission_type'    => 'INPATIENT',
            'discount_category' => 'NONE',
            'hmo_provider'      => 'PhilCare',
            'status'            => 'Active',
        ]);

        // Create encounter invoice: Gross ₱71,428.57 = Patient Copay ₱51,428.57 + HMO ₱10,000.00 + PHIC ₱10,000.00
        $encounterResult = $this->billingService->createAndPostEncounterInvoice([
            'patient_account_id'                  => $patient->id,
            'invoice_date'                        => now()->subDays(15)->toDateString(),
            'statutory_discount'                  => 'NONE',
            'philhealth_amount'                   => '10000.00',
            'philhealth_primary_case_rate_amount' => '10000.00',
            'hmo_provider'                        => 'PhilCare',
            'hmo_amount'                          => '10000.00',
            'hmo_approved_limit'                  => '10000.00',
            'items'                               => [
                [
                    'department'       => 'ROOM_AND_BOARD',
                    'description'      => 'Inpatient Care Suite',
                    'revenue_category' => 'INPATIENT',
                    'quantity'         => 1,
                    'unit_price'       => 71428.57,
                    'is_vatable'       => false,
                ],
            ],
        ]);

        $this->assertEquals('51428.5700', (string) $encounterResult['invoice']->patient_payable);

        // 1. Query Aging Report with Tab = 'ALL'
        $allReport = $this->agingService->getReceivableAgingReport(now()->toDateString(), 'ALL');

        $this->assertEquals('71428.5700', $allReport['grand_total']);
        $this->assertCount(3, $allReport['debtors']);

        // Assert Patient Copay Debtor Row
        $patientDebtor = collect($allReport['debtors'])->firstWhere('debtor_code', 'MRN-2026-DADA1');
        $this->assertNotNull($patientDebtor);
        $this->assertEquals('Patient Copay', $patientDebtor['debtor_type']);
        $this->assertEquals('Dada Pamisa', $patientDebtor['debtor_name']);
        $this->assertEquals('51428.5700', $patientDebtor['total_due']);
        $this->assertEquals('51428.5700', $patientDebtor['current']);
        // Assert Drawer invoices strictly contains copay only
        $this->assertCount(1, $patientDebtor['invoices']);
        $this->assertEquals('Patient Copay', $patientDebtor['invoices'][0]['claim_type']);
        $this->assertEquals('51428.5700', $patientDebtor['invoices'][0]['amount_due']);

        // Assert HMO Debtor Row
        $hmoDebtor = collect($allReport['debtors'])->firstWhere('debtor_type', 'HMO Guarantee Claim');
        $this->assertNotNull($hmoDebtor);
        $this->assertEquals('PhilCare', $hmoDebtor['debtor_name']);
        $this->assertEquals('10000.0000', $hmoDebtor['total_due']);
        $this->assertCount(1, $hmoDebtor['invoices']);
        $this->assertEquals('10000.0000', $hmoDebtor['invoices'][0]['amount_due']);
        $this->assertStringContainsString('PhilCare', $hmoDebtor['invoices'][0]['claim_type']);

        // Assert PhilHealth Debtor Row
        $phicDebtor = collect($allReport['debtors'])->firstWhere('debtor_code', 'PHIC-ACR');
        $this->assertNotNull($phicDebtor);
        $this->assertEquals('PhilHealth Benefit Claims', $phicDebtor['debtor_type']);
        $this->assertEquals('Philippine Health Insurance Corporation (PhilHealth)', $phicDebtor['debtor_name']);
        $this->assertEquals('10000.0000', $phicDebtor['total_due']);
        $this->assertCount(1, $phicDebtor['invoices']);
        $this->assertEquals('10000.0000', $phicDebtor['invoices'][0]['amount_due']);

        // 2. Query Aging Report with Tab = 'PATIENT'
        $patientOnlyReport = $this->agingService->getReceivableAgingReport(now()->toDateString(), 'PATIENT');
        $this->assertEquals('51428.5700', $patientOnlyReport['grand_total']);
        $this->assertCount(1, $patientOnlyReport['debtors']);
        $this->assertEquals('Dada Pamisa', $patientOnlyReport['debtors'][0]['debtor_name']);

        // 3. Query Aging Report with Tab = 'HMO'
        $hmoOnlyReport = $this->agingService->getReceivableAgingReport(now()->toDateString(), 'HMO');
        $this->assertEquals('10000.0000', $hmoOnlyReport['grand_total']);
        $this->assertCount(1, $hmoOnlyReport['debtors']);
        $this->assertEquals('PhilCare', $hmoOnlyReport['debtors'][0]['debtor_name']);

        // 4. Query Aging Report with Tab = 'PHILHEALTH'
        $phicOnlyReport = $this->agingService->getReceivableAgingReport(now()->toDateString(), 'PHILHEALTH');
        $this->assertEquals('10000.0000', $phicOnlyReport['grand_total']);
        $this->assertCount(1, $phicOnlyReport['debtors']);
        $this->assertEquals('Philippine Health Insurance Corporation (PhilHealth)', $phicOnlyReport['debtors'][0]['debtor_name']);

        // 5. Test Controller HTTP View Response
        $response = $this->get(route('ar.ar-aging', ['payor_type' => 'ALL']));
        $response->assertOk();
        $response->assertViewHas('debtors');
        $response->assertSee('Dada Pamisa');
        $response->assertSee('PhilCare');
        $response->assertSee('Philippine Health Insurance Corporation (PhilHealth)');
        $response->assertSee('51,428.57');
    }
}

