<?php

declare(strict_types=1);

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return true;
        }

        $role = $user->role ?? 'StaffAccountant';
        return in_array($role, ['StaffAccountant', 'FinanceManager', 'CFO', 'FinanceDirector'], true);
    }

    public function rules(): array
    {
        return [
            'reference_number' => ['nullable', 'string', 'max:50', 'unique:journal_entries,reference_number'],
            'entry_date'       => ['required', 'date'],
            'description'      => ['required', 'string', 'max:255'],
            'type'             => ['required', 'string', Rule::in(['GENERAL', 'ADJUSTING', 'CLOSING'])],
            'auto_post'        => ['nullable', 'boolean'],
            'lines'            => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.debit'      => ['required', 'numeric', 'min:0'],
            'lines.*.credit'     => ['required', 'numeric', 'min:0'],
            'lines.*.memo'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $lines = $this->input('lines', []);
            if (! is_array($lines) || count($lines) < 2) {
                return;
            }

            $totalDebit = '0.0000';
            $totalCredit = '0.0000';

            foreach ($lines as $line) {
                $debit = (string) ($line['debit'] ?? '0.0000');
                $credit = (string) ($line['credit'] ?? '0.0000');

                $totalDebit = bcadd($totalDebit, $debit, 4);
                $totalCredit = bcadd($totalCredit, $credit, 4);
            }

            if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
                $validator->errors()->add(
                    'lines',
                    "Unbalanced Entry: Total Debits (₱" . number_format((float) $totalDebit, 2) .
                    ") do not equal Total Credits (₱" . number_format((float) $totalCredit, 2) . ")."
                );
            }

            if (bccomp($totalDebit, '0.0000', 4) === 0) {
                $validator->errors()->add('lines', 'Journal entry amounts cannot be 0.00.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'entry_date.required'  => 'Transaction date is required.',
            'description.required' => 'Journal description / narrative is required.',
            'lines.required'       => 'At least two balanced account lines are required.',
            'lines.min'            => 'A valid double-entry journal entry requires at least 2 lines (debit & credit).',
        ];
    }
}
