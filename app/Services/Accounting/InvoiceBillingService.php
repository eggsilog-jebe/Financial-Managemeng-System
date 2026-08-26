<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\PatientInvoiceCreateData;
use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Models\Account;
use App\Models\HmoClaim;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PatientAccount;
use App\Models\PhilhealthClaim;
use App\Models\StatutoryDiscount;
use DomainException;
use Illuminate\Support\Facades\DB;

final class InvoiceBillingService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService,
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Ingest and generate patient discharge billing statement with statutory splits and balanced GL journal entry.
     */
    public function createPatientInvoice(PatientInvoiceCreateData $data): Invoice
    {
        return DB::transaction(function () use ($data): Invoice {
            $patient = PatientAccount::findOrFail($data->patientAccountId);

            // 1. Calculate Gross Total across departmental line items
            $grossTotal = '0.0000';
            $vatReliefTotal = '0.0000';
            $discountTotal = '0.0000';

            $isSeniorOrPwd = in_array($data->discountType, ['SENIOR_CITIZEN', 'PWD'], true);
            $calculatedItems = [];

            foreach ($data->items as $item) {
                $qty = is_array($item) ? (string) ($item['quantity'] ?? '1') : (string) $item->quantity;
                $price = is_array($item) ? (string) ($item['unit_price'] ?? '0') : (string) $item->unitPrice;
                $itemGross = bcmul($qty, $price, 4);
                $grossTotal = bcadd($grossTotal, $itemGross, 4);

                $isVatable = is_array($item) ? (bool) ($item['is_vatable'] ?? true) : (bool) $item->isVatable;
                $isEligible = is_array($item) ? (bool) ($item['is_senior_pwd_eligible'] ?? true) : (bool) $item->isSeniorPwdEligible;

                if ($isSeniorOrPwd && $isEligible) {
                    // RA 9994 / RA 10754: 12% VAT exemption followed by 20% discount
                    if ($isVatable) {
                        $netOfVat = bcdiv($itemGross, '1.1200', 4);
                        $vatRelief = bcsub($itemGross, $netOfVat, 4);
                        $vatReliefTotal = bcadd($vatReliefTotal, $vatRelief, 4);
                        $discount = bcmul($netOfVat, '0.2000', 4);
                    } else {
                        $discount = bcmul($itemGross, '0.2000', 4);
                    }
                    $discountTotal = bcadd($discountTotal, $discount, 4);
                }

                $calculatedItems[] = [
                    'itemCode'        => is_array($item) ? ($item['item_code'] ?? 'ITEM') : $item->itemCode,
                    'description'     => is_array($item) ? ($item['description'] ?? 'Clinical Charge') : $item->description,
                    'department'      => is_array($item) ? ($item['department'] ?? 'CLINICAL') : ($item->department ?? 'CLINICAL'),
                    'revenueCategory' => is_array($item) ? ($item['revenue_category'] ?? 'CLINICAL') : ($item->revenueCategory ?? 'CLINICAL'),
                    'quantity'        => $qty,
                    'unitPrice'       => $price,
                    'gross'           => $itemGross,
                    'isVatable'       => $isVatable,
                    'isEligible'      => $isEligible,
                ];
            }

            // Total Statutory Senior/PWD Deduction
            $totalSeniorPwdDeduction = bcadd($vatReliefTotal, $discountTotal, 4);
            $amountAfterDiscount = bcsub($grossTotal, $totalSeniorPwdDeduction, 4);

            // 2. PhilHealth Case Rate Deductions
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
            $invoiceNumber = $data->invoiceNumber ?? ('INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))));
            $dueDate = $data->dueDate ?? date('Y-m-d', strtotime($data->invoiceDate . ' +30 days'));

            $invoice = Invoice::create([
                'invoice_number'     => $invoiceNumber,
                'patient_account_id' => $patient->id,
                'invoice_date'       => $data->invoiceDate,
                'due_date'           => $dueDate,
                'total_amount'       => $grossTotal,
                'insurance_covered'  => $insuranceCovered,
                'discount_amount'    => $totalSeniorPwdDeduction,
                'vat_amount'         => $vatReliefTotal,
                'patient_payable'    => $patientPayable,
                'paid_amount'        => '0.0000',
                'status'             => 'UNPAID',
            ]);

            // 5. Persist Invoice Line Items
            foreach ($calculatedItems as $c) {
                InvoiceItem::create([
                    'invoice_id'             => $invoice->id,
                    'item_code'              => $c['itemCode'],
                    'description'            => $c['description'],
                    'department'             => $c['department'],
                    'revenue_category'       => $c['revenueCategory'],
                    'quantity'               => $c['quantity'],
                    'unit_price'             => $c['unitPrice'],
                    'gross_amount'           => $c['gross'],
                    'is_vatable'             => $c['isVatable'],
                    'is_senior_pwd_eligible' => $c['isEligible'],
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
                $hfShare = bcmul($philhealthDeduction, '0.6000', 4);
                $pfShare = bcsub($philhealthDeduction, $hfShare, 4);

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
            $this->postRevenueDoubleEntry($invoice, $data->invoiceDate, $data->hmoProvider, $grossTotal, $patientPayable, $philhealthDeduction, $hmoDeduction, $totalSeniorPwdDeduction);

            // CAS Audit Trail
            $this->auditTrailService->logFinancialEvent(
                auditable: $invoice,
                action: 'INSERT',
                oldValues: null,
                newValues: $invoice->toArray(),
                userId: auth()->id(),
                userName: auth()->user()?->name ?? 'Billing Officer',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $invoice->loadMissing(['items', 'philhealthClaim', 'hmoClaims', 'statutoryDiscounts', 'patientAccount']);
        });
    }

    private function postRevenueDoubleEntry(
        Invoice $invoice,
        string $invoiceDate,
        ?string $hmoProvider,
        string $grossTotal,
        string $patientPayable,
        string $philhealthAmount,
        string $hmoAmount,
        string $discountAmount
    ): void {
        $arPatientAccount    = Account::firstOrCreate(['code' => '1010'], ['name' => 'Accounts Receivable - Patients', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $arPhilhealthAccount = Account::firstOrCreate(['code' => '1020'], ['name' => 'Accounts Receivable - PhilHealth', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $arHmoAccount        = Account::firstOrCreate(['code' => '1030'], ['name' => 'Accounts Receivable - Private HMOs', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $discountExpenseAcc  = Account::firstOrCreate(['code' => '5010'], ['name' => 'Senior Citizen & PWD Discounts', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
        $hospitalRevenueAcc  = Account::firstOrCreate(['code' => '4010'], ['name' => 'Hospital Inpatient & Clinical Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);

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
                memo: 'HMO Claim on ' . $invoice->invoice_number . ' (' . ($hmoProvider ?? 'HMO') . ')'
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

        $entryData = new JournalEntryData(
            referenceNumber: 'JE-REV-' . $invoice->invoice_number,
            entryDate: $invoiceDate,
            description: 'Revenue recognition & PhilHealth/HMO split for ' . $invoice->invoice_number,
            type: 'GENERAL',
            postedBy: auth()->id(),
            lines: $journalLines
        );

        $this->journalEntryService->createAndPostEntry($entryData);
    }
}
