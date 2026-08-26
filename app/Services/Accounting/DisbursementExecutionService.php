<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\DisbursementReleaseData;
use App\DTOs\Accounting\DisbursementVoucherData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;
use DomainException;
use Illuminate\Support\Facades\DB;

final class DisbursementExecutionService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Prepare a new Disbursement Voucher for an approved Purchase Bill.
     */
    public function prepareDisbursementVoucher(DisbursementVoucherData $dto, ?int $userId = null): DisbursementVoucher
    {
        return DB::transaction(function () use ($dto, $userId): DisbursementVoucher {
            $bill = PurchaseBill::with('vendor')->findOrFail($dto->purchaseBillId);
            $bank = BankAccount::findOrFail($dto->bankAccountId);

            if ($bill->status === 'PAID') {
                throw new DomainException("Purchase Bill [{$bill->bill_number}] is already fully paid.");
            }

            $unpaidBalance = bcsub((string) $bill->total_amount, (string) $bill->paid_amount, 4);
            $amountToDisburse = $dto->amount;

            if (bccomp($amountToDisburse, '0.0000', 4) <= 0) {
                throw new DomainException("Disbursement amount must be greater than zero.");
            }

            if (bccomp($amountToDisburse, $unpaidBalance, 4) > 0) {
                throw new DomainException("Disbursement amount (₱{$amountToDisburse}) exceeds open balance (₱{$unpaidBalance}).");
            }

            $voucherNum = 'DV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

            $voucher = DisbursementVoucher::create([
                'voucher_number'       => $voucherNum,
                'purchase_bill_id'     => $bill->id,
                'bank_account_id'      => $bank->id,
                'prepared_by'          => $userId ?? auth()->id(),
                'voucher_date'         => $dto->voucherDate,
                'payee_name'           => $dto->payeeName ?? $bill->vendor->name,
                'gross_amount'         => $amountToDisburse,
                'withheld_tax_amount'  => '0.0000',
                'net_disbursed_amount' => $amountToDisburse,
                'payment_method'       => $dto->paymentMethod,
                'check_or_eft_ref'     => $dto->checkOrEftRef,
                'status'               => 'DRAFT',
                'approved_by'          => null,
                'released_at'          => null,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $voucher,
                action: 'INSERT',
                oldValues: null,
                newValues: $voucher->toArray(),
                userId: $userId ?? auth()->id(),
                userName: auth()->user()?->name ?? 'Staff Accountant',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher->loadMissing(['purchaseBill.vendor', 'bankAccount']);
        });
    }

    /**
     * Approve a Disbursement Voucher (Finance Manager / CFO authorization).
     */
    public function approveDisbursementVoucher(int $voucherId, int $userId): DisbursementVoucher
    {
        return DB::transaction(function () use ($voucherId, $userId): DisbursementVoucher {
            $voucher = DisbursementVoucher::findOrFail($voucherId);

            if ($voucher->status === 'RELEASED') {
                throw new DomainException("Disbursement Voucher [{$voucher->voucher_number}] has already been released.");
            }

            $oldValues = $voucher->toArray();

            $voucher->update([
                'status'      => 'APPROVED',
                'approved_by' => $userId,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $voucher,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $voucher->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher->loadMissing(['purchaseBill.vendor', 'bankAccount', 'approver']);
        });
    }

    /**
     * Release payment for an approved Disbursement Voucher via Bank Check or EFT.
     */
    public function releaseDisbursement(
        int $voucherId,
        DisbursementReleaseData $dto,
        int $userId
    ): DisbursementVoucher {
        return DB::transaction(function () use ($voucherId, $dto, $userId): DisbursementVoucher {
            $voucher = DisbursementVoucher::with(['purchaseBill.vendor', 'payrollRun', 'bankAccount'])->findOrFail($voucherId);
            $bill = $voucher->purchaseBill;
            $payroll = $voucher->payrollRun;
            $bank = $voucher->bankAccount;

            if ($voucher->status === 'RELEASED') {
                throw new DomainException("Disbursement Voucher [{$voucher->voucher_number}] is already released.");
            }

            $amount = (string) $voucher->net_disbursed_amount;

            // 1. Check Register if payment method is CHECK
            $checkOrRef = $voucher->check_or_eft_ref;
            if ($voucher->payment_method === 'CHECK') {
                $checkNumber = $dto->checkNumber ?? $voucher->check_or_eft_ref ?? ('CHK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))));
                $checkOrRef = $checkNumber;

                CheckRegister::create([
                    'disbursement_voucher_id' => $voucher->id,
                    'bank_account_id'         => $bank->id,
                    'check_number'            => $checkNumber,
                    'check_date'              => $dto->checkDate ?? date('Y-m-d'),
                    'payee_name'              => $voucher->payee_name,
                    'amount'                  => $amount,
                    'status'                  => 'RELEASED',
                ]);
            } elseif ($dto->eftReference) {
                $checkOrRef = $dto->eftReference;
            }

            // 2. Update Voucher Status
            $oldVoucher = $voucher->toArray();
            $voucher->update([
                'status'           => 'RELEASED',
                'check_or_eft_ref' => $checkOrRef,
                'released_at'      => now(),
                'approved_by'      => $voucher->approved_by ?? $userId,
            ]);

            // 3. Deduct Bank Account Balance
            $bank->decrement('balance', (float) $amount);

            // 4. Update Source Liability
            if ($bill) {
                $newPaid = bcadd((string) $bill->paid_amount, $amount, 4);
                $isFullyPaid = bccomp($newPaid, (string) $bill->total_amount, 4) >= 0;

                $bill->update([
                    'paid_amount' => $newPaid,
                    'status'      => $isFullyPaid ? 'PAID' : 'PARTIAL',
                ]);
            } elseif ($payroll) {
                $payroll->update(['status' => 'DISBURSED']);
            }

            // 5. Post Balanced Double-Entry Journal
            $bankGlCode = $bank->gl_code ?: '1020';
            $cashAccount = Account::firstOrCreate(
                ['code' => $bankGlCode],
                ['name' => "Operating Bank Account - {$bank->bank_name}", 'category' => 'ASSET', 'normal_balance' => 'DEBIT']
            );

            $journalLines = [];

            if ($payroll) {
                $salariesExpenseAcc = Account::firstOrCreate(
                    ['code' => '5020'],
                    ['name' => 'Salaries and Wages Expense', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']
                );
                $statutoryPayableAcc = Account::firstOrCreate(
                    ['code' => '2030'],
                    ['name' => 'Withholding Tax & Statutory Payables', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']
                );

                $grossPay = (string) $payroll->total_gross_pay;
                $statutory = (string) $payroll->total_statutory_deductions;

                $journalLines[] = new JournalLineData(
                    accountId: $salariesExpenseAcc->id,
                    debit: $grossPay,
                    credit: '0.0000',
                    memo: "Gross Payroll disbursement on {$voucher->voucher_number}"
                );
                if (bccomp($statutory, '0.0000', 4) > 0) {
                    $journalLines[] = new JournalLineData(
                        accountId: $statutoryPayableAcc->id,
                        debit: '0.0000',
                        credit: $statutory,
                        memo: "Payroll statutory deductions & withholding taxes"
                    );
                }
                $journalLines[] = new JournalLineData(
                    accountId: $cashAccount->id,
                    debit: '0.0000',
                    credit: $amount,
                    memo: "Net salaries bank release via {$voucher->payment_method}"
                );
            } elseif ($bill) {
                $apAccount = Account::firstOrCreate(
                    ['code' => '2010'],
                    ['name' => 'Accounts Payable - Vendors & Suppliers', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']
                );

                $journalLines[] = new JournalLineData(
                    accountId: $apAccount->id,
                    debit: $amount,
                    credit: '0.0000',
                    memo: "Settlement of Accounts Payable for {$bill->bill_number}"
                );
                $journalLines[] = new JournalLineData(
                    accountId: $cashAccount->id,
                    debit: '0.0000',
                    credit: $amount,
                    memo: "Disbursement via {$voucher->payment_method} on {$voucher->voucher_number}"
                );
            } else {
                $expenseAccount = Account::firstOrCreate(
                    ['code' => '5030'],
                    ['name' => 'General Operating & Departmental Expenses', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']
                );

                $journalLines[] = new JournalLineData(
                    accountId: $expenseAccount->id,
                    debit: $amount,
                    credit: '0.0000',
                    memo: "Disbursement payout for {$voucher->payee_name} ({$voucher->voucher_number})"
                );
                $journalLines[] = new JournalLineData(
                    accountId: $cashAccount->id,
                    debit: '0.0000',
                    credit: $amount,
                    memo: "Disbursement payment release on {$voucher->voucher_number}"
                );
            }

            $entryData = new JournalEntryData(
                referenceNumber: 'JE-DISB-' . $voucher->voucher_number,
                entryDate: date('Y-m-d'),
                description: "Disbursement release [{$voucher->voucher_number}] to {$voucher->payee_name}",
                type: 'GENERAL',
                postedBy: $userId,
                lines: $journalLines
            );

            $this->journalEntryService->createAndPostEntry($entryData);

            // 6. CAS Audit Log
            $this->auditTrailService->logFinancialEvent(
                auditable: $voucher,
                action: 'POST',
                oldValues: $oldVoucher,
                newValues: $voucher->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Treasury Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $voucher->loadMissing(['purchaseBill.vendor', 'payrollRun', 'bankAccount', 'checkRegister', 'approver']);
        });
    }

    /**
     * Backward-compatible release method.
     */
    public function releaseVendorDisbursement(
        int $purchaseBillId,
        int $bankAccountId,
        string $paymentMethod = 'CHECK',
        ?string $checkNumber = null
    ): DisbursementVoucher {
        $voucherData = new DisbursementVoucherData(
            purchaseBillId: $purchaseBillId,
            bankAccountId: $bankAccountId,
            voucherDate: date('Y-m-d'),
            amount: (string) PurchaseBill::findOrFail($purchaseBillId)->balance_due,
            paymentMethod: $paymentMethod,
            checkOrEftRef: $checkNumber,
        );

        $voucher = $this->prepareDisbursementVoucher($voucherData);
        $this->approveDisbursementVoucher($voucher->id, auth()->id() ?? 1);

        $releaseData = new DisbursementReleaseData(
            checkNumber: $checkNumber,
            checkDate: date('Y-m-d'),
        );

        return $this->releaseDisbursement($voucher->id, $releaseData, auth()->id() ?? 1);
    }
}
