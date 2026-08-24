<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use App\Models\PurchaseBill;
use DomainException;
use Illuminate\Support\Facades\DB;

final class DisbursementExecutionService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService
    ) {}

    /**
     * Release payment for an approved Purchase Bill via Bank Check or PESONet EFT.
     */
    public function releaseVendorDisbursement(
        int $purchaseBillId,
        int $bankAccountId,
        string $paymentMethod = 'CHECK', // CHECK, PESONET_EFT, INSTAPAY
        ?string $checkNumber = null
    ): DisbursementVoucher {
        return DB::transaction(function () use ($purchaseBillId, $bankAccountId, $paymentMethod, $checkNumber): DisbursementVoucher {
            $bill = PurchaseBill::with('vendor')->findOrFail($purchaseBillId);
            $bank = BankAccount::findOrFail($bankAccountId);

            if ($bill->status === 'PAID') {
                throw new DomainException("Purchase Bill [{$bill->bill_number}] is already fully paid.");
            }

            // Calculate remaining payable balance
            $balanceToPay = bcsub((string) $bill->total_amount, (string) $bill->paid_amount, 4);

            $voucherNum = 'DV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $voucher = DisbursementVoucher::create([
                'voucher_number'       => $voucherNum,
                'purchase_bill_id'     => $bill->id,
                'bank_account_id'      => $bank->id,
                'voucher_date'         => date('Y-m-d'),
                'payee_name'           => $bill->vendor->name,
                'gross_amount'         => $bill->total_amount,
                'withheld_tax_amount'  => '0.0000',
                'net_disbursed_amount' => $balanceToPay,
                'payment_method'       => $paymentMethod,
                'check_or_eft_ref'     => $checkNumber ?? ('EFT-' . strtoupper(bin2hex(random_bytes(3)))),
                'status'               => 'RELEASED',
                'approved_by'          => auth()->id(),
                'released_at'          => now(),
            ]);

            if ($paymentMethod === 'CHECK' && $checkNumber) {
                CheckRegister::create([
                    'disbursement_voucher_id' => $voucher->id,
                    'bank_account_id'         => $bank->id,
                    'check_number'            => $checkNumber,
                    'check_date'              => date('Y-m-d'),
                    'payee_name'              => $bill->vendor->name,
                    'amount'                  => $balanceToPay,
                    'status'                  => 'ISSUED',
                ]);
            }

            // Update Purchase Bill
            $newPaid = bcadd((string) $bill->paid_amount, $balanceToPay, 4);
            $bill->update([
                'paid_amount' => $newPaid,
                'status'      => 'PAID',
            ]);

            // Deduct Bank Account Balance
            $bank->decrement('balance', (float) $balanceToPay);

            // Post Double-Entry Journal: DR AP - Vendors, CR Cash in Bank
            $apAccount = Account::firstOrCreate(['code' => '2010'], ['name' => 'Accounts Payable - Vendors & Suppliers', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']);
            $cashAccount = Account::firstOrCreate(['code' => '1020'], ['name' => 'Operating Bank Account - Metrobank', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);

            $journalLines = [
                new JournalLineData(
                    accountId: $apAccount->id,
                    debit: $balanceToPay,
                    credit: '0.0000',
                    memo: "Settlement of Accounts Payable for {$bill->bill_number}"
                ),
                new JournalLineData(
                    accountId: $cashAccount->id,
                    debit: '0.0000',
                    credit: $balanceToPay,
                    memo: "Disbursement via {$paymentMethod} on {$voucher->voucher_number}"
                ),
            ];

            $entryData = new JournalEntryData(
                referenceNumber: $voucher->voucher_number,
                entryDate: date('Y-m-d'),
                description: "Vendor disbursement for {$bill->bill_number} to {$bill->vendor->name}",
                type: 'GENERAL',
                postedBy: auth()->id(),
                lines: $journalLines
            );

            $this->journalEntryService->createAndPostEntry($entryData);

            return $voucher->loadMissing(['purchaseBill', 'bankAccount', 'checkRegister']);
        });
    }
}
