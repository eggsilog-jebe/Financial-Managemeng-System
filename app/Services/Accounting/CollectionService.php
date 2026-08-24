<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\DTOs\PaymentReceiptData;
use App\Models\Account;
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\OfficialReceipt;
use App\Models\PatientAccount;
use App\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;

final class CollectionService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService
    ) {}

    /**
     * Process cashier payment settlement, issue official BIR receipt, deduct patient AR,
     * and post balanced double-entry collection journal.
     */
    public function processCollection(PaymentReceiptData $data): Payment
    {
        return DB::transaction(function () use ($data): Payment {
            $patient = PatientAccount::findOrFail($data->patientAccountId);
            $paymentDate = $data->paymentDate ?: date('Y-m-d');

            // 1. Create Payment Record
            $paymentRef = 'PAY-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $payment = Payment::create([
                'payment_reference'      => $paymentRef,
                'invoice_id'             => $data->invoiceId,
                'patient_account_id'     => $patient->id,
                'cashier_shift_id'       => $data->cashierShiftId,
                'payment_date'           => $paymentDate,
                'amount'                 => $data->amount,
                'payment_method'         => $data->paymentMethod,
                'transaction_channel_ref'=> $data->transactionChannelRef,
                'payment_type'           => $data->paymentType,
            ]);

            // 2. Generate BIR Official Receipt (OR)
            $orNumber = 'OR-' . date('Y') . '-' . str_pad((string) (OfficialReceipt::count() + 1), 6, '0', STR_PAD_LEFT);
            OfficialReceipt::create([
                'or_number'              => $orNumber,
                'payment_id'             => $payment->id,
                'invoice_id'             => $data->invoiceId,
                'patient_account_id'     => $patient->id,
                'or_date'                => $paymentDate,
                'payor_name'             => $data->payorName ?: $patient->full_name,
                'payor_tin'              => $data->payorTin,
                'vatable_sales'          => '0.0000',
                'vat_exempt_sales'       => $data->amount,
                'zero_rated_sales'       => '0.0000',
                'vat_amount'             => '0.0000',
                'total_amount_collected' => $data->amount,
                'status'                 => 'VALID',
            ]);

            // 3. Deduct Patient AR & Invoice Balance
            if ($data->invoiceId) {
                $invoice = Invoice::find($data->invoiceId);
                if ($invoice) {
                    $newPayable = bcsub((string) $invoice->patient_payable, $data->amount, 4);
                    $newStatus = bccomp($newPayable, '0.0000', 4) <= 0 ? 'SETTLED' : 'PARTIAL';
                    $invoice->update([
                        'patient_payable' => bccomp($newPayable, '0.0000', 4) < 0 ? '0.0000' : $newPayable,
                        'status'          => $newStatus,
                    ]);
                }
            }

            $currentBal = (string) $patient->current_balance;
            $newBal = bcsub($currentBal, $data->amount, 4);
            $patient->update([
                'current_balance' => bccomp($newBal, '0.0000', 4) < 0 ? '0.0000' : $newBal,
            ]);

            // 4. Update Cashier Shift Collections
            if ($data->cashierShiftId) {
                $shift = CashierShift::find($data->cashierShiftId);
                if ($shift && $shift->status === 'OPEN') {
                    if ($data->paymentMethod === 'CASH') {
                        $shift->increment('expected_cash', (float) $data->amount);
                    } else {
                        $shift->increment('total_digital_collections', (float) $data->amount);
                    }
                    $shift->increment('total_collections', (float) $data->amount);
                }
            }

            // 5. Post General Ledger Collection Journal Entry
            $this->postCollectionJournal($payment, $data, $paymentDate);

            return $payment->loadMissing(['officialReceipt', 'patientAccount', 'invoice']);
        });
    }

    private function postCollectionJournal(Payment $payment, PaymentReceiptData $data, string $paymentDate): void
    {
        // Select Cash / Bank Asset Account depending on settlement channel
        $debitAccountCode = match ($data->paymentMethod) {
            'CASH'                     => '1010', // Cash on Hand - Cashier Drawer / Vault
            'GCASH', 'MAYA', 'QR_PH'   => '1021', // Digital Merchant Settlement Clearing Account
            default                    => '1020', // Bank Operating Account / Merchant Card
        };

        $cashAssetAccount = Account::firstOrCreate(
            ['code' => $debitAccountCode],
            ['name' => $debitAccountCode === '1010' ? 'Cash on Hand - Cashier Drawer' : 'Digital Merchant & Bank Clearing', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
        );

        $arPatientAccount = Account::firstOrCreate(
            ['code' => '1010'], // AR Patient
            ['name' => 'Accounts Receivable - Patients', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
        );

        $journalLines = [
            new JournalLineData(
                accountId: $cashAssetAccount->id,
                debit: $data->amount,
                credit: '0.0000',
                memo: "Collection receipt {$payment->payment_reference} via {$data->paymentMethod}"
            ),
            new JournalLineData(
                accountId: $arPatientAccount->id,
                debit: '0.0000',
                credit: $data->amount,
                memo: "Settlement of patient AR on {$payment->payment_reference}"
            ),
        ];

        $entryData = new JournalEntryData(
            referenceNumber: $payment->payment_reference,
            entryDate: $paymentDate,
            description: "Cashier collection settlement [{$data->paymentMethod}] for {$payment->payment_reference}",
            type: 'GENERAL',
            postedBy: auth()->id(),
            lines: $journalLines
        );

        $this->journalEntryService->createAndPostEntry($entryData);
    }
}
