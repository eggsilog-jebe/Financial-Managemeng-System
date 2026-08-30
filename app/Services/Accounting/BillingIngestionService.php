<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\DTOs\PatientBillingIngestionData;
use App\Models\Account;
use App\Models\HmoClaim;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PatientAccount;
use App\Models\PhilhealthClaim;
use App\Models\StatutoryDiscount;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class BillingIngestionService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService
    ) {}

    /**
     * Ingest clinical encounter billables, compute PhilHealth & Senior/PWD statutory splits,
     * and automatically post balanced double-entry revenue journal entries.
     */
    public function ingestAndPostPatientBill(PatientBillingIngestionData $data): Invoice
    {
        return DB::transaction(function () use ($data): Invoice {
            $patient = PatientAccount::findOrFail($data->patientAccountId);

            // 1. Calculate Gross Total across clinical billables
            $grossTotal = '0.0000';
            $vatReliefTotal = '0.0000';
            $discountTotal = '0.0000';

            $isSeniorOrPwd = in_array($data->discountType, ['SENIOR_CITIZEN', 'PWD'], true);

            foreach ($data->items as $item) {
                $itemGross = $item->getGrossAmount();
                $grossTotal = bcadd($grossTotal, $itemGross, 4);

                if ($isSeniorOrPwd && $item->isSeniorPwdEligible) {
                    // RA 9994 / RA 10754: 12% VAT exemption followed by 20% discount on net-of-VAT amount
                    if ($item->isVatable) {
                        // Formula: Net of VAT = Gross / 1.12. VAT Relief = Gross - Net
                        $netOfVat = bcdiv($itemGross, '1.1200', 4);
                        $vatRelief = bcsub($itemGross, $netOfVat, 4);
                        $vatReliefTotal = bcadd($vatReliefTotal, $vatRelief, 4);
                        $discount = bcmul($netOfVat, '0.2000', 4);
                    } else {
                        $discount = bcmul($itemGross, '0.2000', 4);
                    }
                    $discountTotal = bcadd($discountTotal, $discount, 4);
                }
            }

            // Total Statutory Deduction
            $totalSeniorPwdDeduction = bcadd($vatReliefTotal, $discountTotal, 4);
            $amountAfterDiscount = bcsub($grossTotal, $totalSeniorPwdDeduction, 4);

            // 2. PhilHealth All-Case-Rate (ACR) Deduction
            $philhealthTotal = bcadd($data->philhealthPrimaryCaseRateAmount, $data->philhealthSecondaryCaseRateAmount, 4);
            $philhealthDeduction = bccomp($amountAfterDiscount, $philhealthTotal, 4) >= 0 ? $philhealthTotal : $amountAfterDiscount;
            $amountAfterPhilhealth = bcsub($amountAfterDiscount, $philhealthDeduction, 4);

            // 3. Private HMO Coverage Deduction
            $hmoDeduction = '0.0000';
            if ($data->hmoProvider !== null && bccomp($data->hmoApprovedLimit, '0.0000', 4) > 0) {
                $hmoDeduction = bccomp($amountAfterPhilhealth, $data->hmoApprovedLimit, 4) >= 0
                    ? $data->hmoApprovedLimit
                    : $amountAfterPhilhealth;
            }
            $patientPayable = bcsub($amountAfterPhilhealth, $hmoDeduction, 4);
            $insuranceCovered = bcadd($philhealthDeduction, $hmoDeduction, 4);

            // 4. Create Master Invoice
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $invoice = Invoice::create([
                'invoice_number'     => $invoiceNumber,
                'patient_account_id' => $patient->id,
                'invoice_date'       => $data->invoiceDate,
                'total_amount'       => $grossTotal,
                'insurance_covered'  => $insuranceCovered,
                'patient_payable'    => $patientPayable,
                'status'             => bccomp($patientPayable, '0.0000', 4) === 0 ? 'SETTLED' : 'UNPAID',
            ]);

            // 5. Persist Invoice Items
            foreach ($data->items as $item) {
                InvoiceItem::create([
                    'invoice_id'             => $invoice->id,
                    'item_code'              => $item->itemCode,
                    'description'            => $item->description,
                    'department'             => $item->department,
                    'revenue_category'       => $item->revenueCategory,
                    'quantity'               => $item->quantity,
                    'unit_price'             => $item->unitPrice,
                    'gross_amount'           => $item->getGrossAmount(),
                    'is_vatable'             => $item->isVatable,
                    'is_senior_pwd_eligible' => $item->isSeniorPwdEligible,
                ]);
            }

            // 6. Record Statutory Discount
            if (bccomp($totalSeniorPwdDeduction, '0.0000', 4) > 0) {
                StatutoryDiscount::create([
                    'invoice_id'        => $invoice->id,
                    'discount_type'     => $data->discountType ?? 'SENIOR_CITIZEN',
                    'id_card_number'    => $data->idCardNumber,
                    'vat_exempt_amount' => $vatReliefTotal,
                    'discount_rate'     => '0.2000',
                    'discount_amount'   => $totalSeniorPwdDeduction,
                ]);
            }

            // 7. Record PhilHealth Claim
            if (bccomp($philhealthDeduction, '0.0000', 4) > 0) {
                $hfShare = bcmul($philhealthDeduction, '0.6000', 4); // 60% Hospital Fee
                $pfShare = bcsub($philhealthDeduction, $hfShare, 4); // 40% Professional Fee

                PhilhealthClaim::create([
                    'invoice_id'                  => $invoice->id,
                    'claim_series_number'         => 'PHIC-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
                    'member_pin'                  => $data->philhealthMemberPin,
                    'patient_pin'                 => $data->philhealthMemberPin,
                    'membership_type'             => 'EMPLOYED',
                    'primary_icd_code'            => $data->philhealthPrimaryIcd,
                    'primary_case_rate_code'      => $data->philhealthPrimaryCaseCode,
                    'primary_case_rate_amount'    => $data->philhealthPrimaryCaseRateAmount,
                    'secondary_case_rate_code'    => $data->philhealthSecondaryCaseCode,
                    'secondary_case_rate_amount'  => $data->philhealthSecondaryCaseRateAmount,
                    'total_case_rate_amount'      => $philhealthDeduction,
                    'hospital_fee_share'          => $hfShare,
                    'professional_fee_share'      => $pfShare,
                    'claim_status'                => 'TRANSMITTED',
                    'transmitted_at'              => $data->invoiceDate,
                ]);
            }

            // 8. Record HMO Claim
            if (bccomp($hmoDeduction, '0.0000', 4) > 0 && $data->hmoProvider !== null) {
                HmoClaim::create([
                    'invoice_id'      => $invoice->id,
                    'hmo_provider'    => $data->hmoProvider,
                    'loa_number'      => $data->hmoLoaNumber,
                    'card_number'     => $data->hmoCardNumber,
                    'approved_limit'  => $data->hmoApprovedLimit,
                    'claimed_amount'  => $hmoDeduction,
                    'settled_amount'  => '0.0000',
                    'status'          => 'SUBMITTED',
                ]);
            }

            // 9. Update Patient Account Balances
            $patient->increment('total_billed', (float) $grossTotal);
            $patient->increment('current_balance', (float) $patientPayable);

            // 10. Post Double-Entry Journal to General Ledger
            $this->postRevenueDoubleEntry($invoice, $data, $grossTotal, $patientPayable, $philhealthDeduction, $hmoDeduction, $totalSeniorPwdDeduction);

            return $invoice->load(['items', 'philhealthClaim', 'hmoClaims', 'statutoryDiscounts']);
        });
    }

    private function postRevenueDoubleEntry(
        Invoice $invoice,
        PatientBillingIngestionData $data,
        string $grossTotal,
        string $patientPayable,
        string $philhealthAmount,
        string $hmoAmount,
        string $discountAmount
    ): void {
        // Resolve Accounts
        $arPatientAccount    = Account::firstOrCreate(['code' => '1110'], ['name' => 'Accounts Receivable - Patient Copay', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $arPhilhealthAccount = Account::firstOrCreate(['code' => '1120'], ['name' => 'Accounts Receivable - PhilHealth Claims', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $arHmoAccount        = Account::firstOrCreate(['code' => '1130'], ['name' => 'Accounts Receivable - HMO Claims', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $discountExpenseAcc  = Account::firstOrCreate(['code' => '4910'], ['name' => 'Statutory Discounts Allowed (Senior/PWD)', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
        $hospitalRevenueAcc  = Account::firstOrCreate(['code' => '4010'], ['name' => 'Inpatient Hospital Care Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);

        $journalLines = [];

        // Debit: Patient Out-of-Pocket AR
        if (bccomp($patientPayable, '0.0000', 4) > 0) {
            $journalLines[] = new JournalLineData(
                accountId: $arPatientAccount->id,
                debit: $patientPayable,
                credit: '0.0000',
                memo: 'Patient copay balance on ' . $invoice->invoice_number
            );
        }

        // Debit: PhilHealth AR
        if (bccomp($philhealthAmount, '0.0000', 4) > 0) {
            $journalLines[] = new JournalLineData(
                accountId: $arPhilhealthAccount->id,
                debit: $philhealthAmount,
                credit: '0.0000',
                memo: 'PhilHealth ACR Claim on ' . $invoice->invoice_number
            );
        }

        // Debit: HMO AR
        if (bccomp($hmoAmount, '0.0000', 4) > 0) {
            $journalLines[] = new JournalLineData(
                accountId: $arHmoAccount->id,
                debit: $hmoAmount,
                credit: '0.0000',
                memo: 'HMO Claim on ' . $invoice->invoice_number . ' (' . ($data->hmoProvider ?? 'HMO') . ')'
            );
        }

        // Debit: Senior/PWD Statutory Discount Expense
        if (bccomp($discountAmount, '0.0000', 4) > 0) {
            $journalLines[] = new JournalLineData(
                accountId: $discountExpenseAcc->id,
                debit: $discountAmount,
                credit: '0.0000',
                memo: 'Statutory 20% Senior/PWD & VAT relief on ' . $invoice->invoice_number
            );
        }

        // Credit: Total Hospital Clinical Revenue
        $journalLines[] = new JournalLineData(
            accountId: $hospitalRevenueAcc->id,
            debit: '0.0000',
            credit: $grossTotal,
            memo: 'Clinical gross revenue recognition on ' . $invoice->invoice_number
        );

        // Verify Double-Entry Balance
        $entryData = new JournalEntryData(
            referenceNumber: $invoice->invoice_number,
            entryDate: $data->invoiceDate,
            description: 'Revenue recognition & PhilHealth/HMO split for ' . $invoice->invoice_number,
            type: 'GENERAL',
            postedBy: auth()->id(),
            lines: $journalLines
        );

        $this->journalEntryService->createAndPostEntry($entryData);
    }
}
