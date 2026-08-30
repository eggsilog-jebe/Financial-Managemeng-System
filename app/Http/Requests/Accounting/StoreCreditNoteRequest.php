<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id'         => ['required', 'integer', 'exists:invoices,id'],
            'amount'             => ['required', 'numeric', 'gt:0'],
            'reason'             => ['sometimes', 'required_without:adjustment_type', 'string', 'max:255'],
            'adjustment_type'    => ['sometimes', 'required_without:reason', 'string', 'max:255'],
            'issue_date'         => ['required', 'date'],
            'save_as_draft'      => ['sometimes', 'nullable'],
            'is_draft'           => ['sometimes', 'nullable'],
            'patient_account_id' => ['nullable', 'integer', 'exists:patient_accounts,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            if ($this->filled('invoice_id')) {
                $invoice = Invoice::with(['statutoryDiscounts', 'creditNotes'])->find($this->input('invoice_id'));
                if ($invoice) {
                    $reason = (string) ($this->input('reason') ?: $this->input('adjustment_type'));
                    $statutoryTypes = ['SENIOR_CITIZEN_DISCOUNT', 'PWD_DISCOUNT', 'SENIOR_CITIZEN', 'PWD'];

                    // 1. Prevent Duplicate Statutory Discounts on Same Invoice (RA 9994 / RA 10754)
                    if (in_array($reason, $statutoryTypes, true)) {
                        $hasInitialStatutory = $invoice->statutoryDiscounts->isNotEmpty();
                        $hasExistingStatutoryCN = $invoice->creditNotes
                            ->whereIn('reason', $statutoryTypes)
                            ->whereIn('status', ['POSTED', 'APPLIED', 'DRAFT'])
                            ->isNotEmpty();

                        if ($hasInitialStatutory || $hasExistingStatutoryCN) {
                            $v->errors()->add(
                                'reason',
                                'Only one statutory discount (Senior Citizen or PWD) is allowed per invoice under RA 9994 / RA 10754. Please void the existing statutory credit note first.'
                            );
                            $v->errors()->add(
                                'adjustment_type',
                                'Only one statutory discount (Senior Citizen or PWD) is allowed per invoice under RA 9994 / RA 10754. Please void the existing statutory credit note first.'
                            );
                        }
                    }

                    // 2. Open Copay Balance Validation
                    if ($this->filled('amount')) {
                        $openBalance = $invoice->balance_due;
                        $amount = (string) $this->input('amount');

                        if (bccomp($amount, $openBalance, 4) > 0) {
                            $v->errors()->add(
                                'amount',
                                "Credit Note amount (₱" . number_format((float) $amount, 2) . ") exceeds invoice open copay balance (₱" . number_format((float) $openBalance, 2) . ")."
                            );
                        }
                    }
                }
            }
        });
    }
}
