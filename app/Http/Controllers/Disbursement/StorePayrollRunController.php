<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\DTOs\PayrollEmployeeItemData;
use App\DTOs\PayrollRunIngestionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IngestPayrollRunRequest;
use App\Services\Accounting\PayrollIntegrationService;
use Illuminate\Http\RedirectResponse;

final class StorePayrollRunController extends Controller
{
    public function __invoke(
        IngestPayrollRunRequest $request,
        PayrollIntegrationService $service
    ): RedirectResponse {
        $validated = $request->validated();

        $employees = array_map(
            fn (array $emp) => new PayrollEmployeeItemData(
                employeeIdNumber: (string) $emp['employee_id_number'],
                employeeName: (string) $emp['employee_name'],
                department: (string) $emp['department'],
                basicSalary: (string) $emp['basic_salary'],
                overtimePay: isset($emp['overtime_pay']) && $emp['overtime_pay'] !== '' ? (string) $emp['overtime_pay'] : '0.0000',
                allowances: isset($emp['allowances']) && $emp['allowances'] !== '' ? (string) $emp['allowances'] : '0.0000',
                tin: $emp['tin'] ?? null,
                sssNumber: $emp['sss_number'] ?? null,
                philhealthNumber: $emp['philhealth_number'] ?? null,
                pagibigNumber: $emp['pagibig_number'] ?? null,
                bankAccountNumber: $emp['bank_account_number'] ?? null,
            ),
            $validated['employees']
        );

        $dto = new PayrollRunIngestionData(
            cutoffStart: (string) $validated['cutoff_start'],
            cutoffEnd: (string) $validated['cutoff_end'],
            payoutDate: (string) $validated['payout_date'],
            disbursementBankAccountId: (int) $validated['disbursement_bank_account_id'],
            employees: $employees,
        );

        try {
            $payrollRun = $service->ingestAndDisbursePayroll($dto);

            return redirect()->route('disbursement.payment-requests')->with(
                'success',
                "Payroll Run [{$payrollRun->payroll_run_number}] successfully posted for {$payrollRun->employee_count} personnel. Net Disbursed: ₱" . number_format((float) $payrollRun->total_net_pay, 2) . " with automated Double-Entry GL entries."
            );
        } catch (\DomainException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to process payroll run: ' . $e->getMessage());
        }
    }
}
