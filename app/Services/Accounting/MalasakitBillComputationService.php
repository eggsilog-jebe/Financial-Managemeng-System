<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\GuaranteeLetter;
use App\Models\Invoice;
use App\Models\PatientAccount;
use Illuminate\Support\Collection;

final class MalasakitBillComputationService
{
    /**
     * Compute the full Philippine Public Hospital financial assistance waterfall.
     *
     * @param array<int, array{item_code?: string, description: string, quantity: string|float|int, unit_price: string|float|int, is_vatable?: bool, is_eligible?: bool}> $items
     * @return array{
     *     gross_total: string,
     *     vat_exempt_relief: string,
     *     statutory_discount: string,
     *     discount_rule_applied: string,
     *     amount_after_discounts: string,
     *     philhealth_deduction: string,
     *     amount_after_philhealth: string,
     *     is_nbb_covered: bool,
     *     nbb_subsidy_amount: string,
     *     hmo_deduction: string,
     *     guarantee_letters_applied: array<int, array{agency: string, gl_number: string, amount: string}>,
     *     total_government_assistance: string,
     *     net_patient_payable: string
     * }
     */
    public function computeWaterfall(
        PatientAccount $patient,
        array $items,
        string $philhealthPrimaryCaseRate = '0.0000',
        string $philhealthSecondaryCaseRate = '0.0000',
        string $hmoApprovedLimit = '0.0000',
        ?Collection $applicableGuaranteeLetters = null
    ): array {
        // 1. Calculate Gross Total across departmental line items
        $grossTotal = '0.0000';
        $vatExemptRelief = '0.0000';
        $statutoryDiscount = '0.0000';
        $discountRule = 'NONE';

        $discountCat = strtoupper((string) ($patient->discount_category ?? 'NONE'));
        $isSenior = in_array($discountCat, ['SENIOR_CITIZEN', 'SENIOR'], true);
        $isPwd = in_array($discountCat, ['PWD', 'PWD_DISCOUNT'], true);
        $isSoloParent = in_array($discountCat, ['SOLO_PARENT'], true);

        // Anti-stacking rule (RA 9994 Sec 5 / RA 10754): An individual who is both SC and PWD
        // shall avail of the discount under ONE law only (single 20% discount).
        if ($isSenior) {
            $discountRule = 'RA 9994 (Senior Citizen 20% + 12% VAT Exemption)';
        } elseif ($isPwd) {
            $discountRule = 'RA 10754 (Persons with Disability 20% + 12% VAT Exemption)';
        } elseif ($isSoloParent) {
            $discountRule = 'RA 11861 (Expanded Solo Parents Welfare Act 10%)';
        }

        foreach ($items as $item) {
            $qty = (string) ($item['quantity'] ?? '1');
            $price = (string) ($item['unit_price'] ?? '0');
            $itemGross = bcmul($qty, $price, 4);
            $grossTotal = bcadd($grossTotal, $itemGross, 4);

            $isVatable = (bool) ($item['is_vatable'] ?? true);
            $isEligible = (bool) ($item['is_eligible'] ?? true);

            if (($isSenior || $isPwd) && $isEligible) {
                if ($isVatable) {
                    $netOfVat = bcdiv($itemGross, '1.1200', 4);
                    $vatRelief = bcsub($itemGross, $netOfVat, 4);
                    $vatExemptRelief = bcadd($vatExemptRelief, $vatRelief, 4);
                    $disc = bcmul($netOfVat, '0.2000', 4);
                } else {
                    $disc = bcmul($itemGross, '0.2000', 4);
                }
                $statutoryDiscount = bcadd($statutoryDiscount, $disc, 4);
            } elseif ($isSoloParent && $isEligible) {
                $disc = bcmul($itemGross, '0.1000', 4);
                $statutoryDiscount = bcadd($statutoryDiscount, $disc, 4);
            }
        }

        $totalStatutoryDeduction = bcadd($vatExemptRelief, $statutoryDiscount, 4);
        $amountAfterDiscounts = bcsub($grossTotal, $totalStatutoryDeduction, 4);
        if (bccomp($amountAfterDiscounts, '0.0000', 4) < 0) {
            $amountAfterDiscounts = '0.0000';
        }

        // 2. PhilHealth Case Rate Deductions (RA 11223 Universal Health Care)
        $philhealthTotal = bcadd($philhealthPrimaryCaseRate, $philhealthSecondaryCaseRate, 4);
        $philhealthDeduction = bccomp($amountAfterDiscounts, $philhealthTotal, 4) >= 0
            ? $philhealthTotal
            : $amountAfterDiscounts;
        $amountAfterPhilhealth = bcsub($amountAfterDiscounts, $philhealthDeduction, 4);

        // 3. No Balance Billing (NBB Policy - PhilHealth Circular 2017-0017)
        // For indigent, sponsored, 4Ps, or declared NBB ward patients, out-of-pocket is strictly 0.
        $isNbbCovered = (bool) ($patient->is_nbb || in_array($patient->patient_type, ['indigent', '4ps'], true));
        $nbbSubsidyAmount = '0.0000';

        if ($isNbbCovered && bccomp($amountAfterPhilhealth, '0.0000', 4) > 0) {
            $nbbSubsidyAmount = $amountAfterPhilhealth;
            $amountAfterPhilhealth = '0.0000';
        }

        // 4. Private HMO Deduction (if non-NBB)
        $hmoDeduction = '0.0000';
        if (! $isNbbCovered && bccomp($hmoApprovedLimit, '0.0000', 4) > 0) {
            $hmoDeduction = bccomp($amountAfterPhilhealth, $hmoApprovedLimit, 4) >= 0
                ? $hmoApprovedLimit
                : $amountAfterPhilhealth;
        }
        $amountAfterHmo = bcsub($amountAfterPhilhealth, $hmoDeduction, 4);

        // 5. Malasakit Center Guarantee Letters (RA 11463): PCSO, DSWD, DOH-MAIP
        $appliedGls = [];
        $totalGlAssistance = '0.0000';
        $remainingBalance = $amountAfterHmo;

        $gls = $applicableGuaranteeLetters ?? $patient->guaranteeLetters()->where('status', 'ACTIVE')->get();

        foreach ($gls as $gl) {
            if (bccomp($remainingBalance, '0.0000', 4) <= 0) {
                break;
            }

            $availableGl = (string) $gl->remaining_amount;
            if (bccomp($availableGl, '0.0000', 4) <= 0) {
                continue;
            }

            $glDeduction = bccomp($remainingBalance, $availableGl, 4) >= 0
                ? $availableGl
                : $remainingBalance;

            $appliedGls[] = [
                'agency'    => $gl->issuing_agency,
                'gl_number' => $gl->gl_number,
                'amount'    => $glDeduction,
            ];

            $totalGlAssistance = bcadd($totalGlAssistance, $glDeduction, 4);
            $remainingBalance = bcsub($remainingBalance, $glDeduction, 4);
        }

        $totalGovernmentAssistance = bcadd($nbbSubsidyAmount, $totalGlAssistance, 4);
        $netPatientPayable = bccomp($remainingBalance, '0.0000', 4) > 0 ? $remainingBalance : '0.0000';

        return [
            'gross_total'                 => $grossTotal,
            'vat_exempt_relief'           => $vatExemptRelief,
            'statutory_discount'          => $statutoryDiscount,
            'discount_rule_applied'       => $discountRule,
            'amount_after_discounts'      => $amountAfterDiscounts,
            'philhealth_deduction'        => $philhealthDeduction,
            'amount_after_philhealth'     => $amountAfterPhilhealth,
            'is_nbb_covered'              => $isNbbCovered,
            'nbb_subsidy_amount'          => $nbbSubsidyAmount,
            'hmo_deduction'               => $hmoDeduction,
            'guarantee_letters_applied'   => $appliedGls,
            'total_government_assistance' => $totalGovernmentAssistance,
            'net_patient_payable'         => $netPatientPayable,
        ];
    }
}
