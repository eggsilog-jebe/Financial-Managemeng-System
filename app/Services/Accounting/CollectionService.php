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

            // 3. Update Invoice & Patient Balance (Preserve immutable patient_payable obligation)
            if ($data->invoiceId) {
                $invoice = Invoice::where('id', $data->invoiceId)->lockForUpdate()->first();
                if ($invoice) {
                    if (bccomp((string) $data->amount, (string) $invoice->balance_due, 4) > 0) {
                        throw new DomainException("Collection amount (₱{$data->amount}) exceeds open invoice balance (₱{$invoice->balance_due}).");
                    }
                    $newPaid = bcadd((string) $invoice->paid_amount, (string) $data->amount, 4);
                    $newStatus = bccomp($newPaid, (string) $invoice->patient_payable, 4) >= 0 ? 'SETTLED' : 'PARTIAL';
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'status'      => $newStatus,
                    ]);
                }
            }

            $currentBal = (string) $patient->current_balance;
            $newBal = bcsub($currentBal, $data->amount, 4);
            $patient->update([
                'current_balance' => bccomp($newBal, '0.0000', 4) < 0 ? '0.0000' : $newBal,
            ]);

            // 4. Update Cashier Shift Collections using BCMath (no float arithmetic)
            if ($data->cashierShiftId) {
                $shift = CashierShift::find($data->cashierShiftId);
                if ($shift && $shift->status === 'OPEN') {
                    if ($data->paymentMethod === 'CASH') {
                        $shift->update([
                            'expected_cash' => bcadd((string) $shift->expected_cash, $data->amount, 4),
                        ]);
                    } else {
                        $shift->update([
                            'total_digital_collections' => bcadd((string) $shift->total_digital_collections, $data->amount, 4),
                        ]);
                    }
                    $shift->update([
                        'total_collections' => bcadd((string) $shift->total_collections, $data->amount, 4),
                    ]);
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
            'CASH'                   => '1011', // Cashier Undeposited Collections
            'GCASH', 'MAYA', 'QR_PH' => '1002', // Digital Collections & POS Clearing
            default                  => '1002', // Merchant Card / Digital Clearing
        };

        $cashAssetAccount = Account::firstOrCreate(
            ['code' => $debitAccountCode],
            ['name' => $debitAccountCode === '1011' ? 'Cashier Undeposited Collections' : 'Digital Collections & POS Clearing', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
        );

        $arPatientAccount = Account::firstOrCreate(
            ['code' => '1110'],
            ['name' => 'Accounts Receivable - Patient Copay', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
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
