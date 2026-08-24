<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTOs\PayrollEmployeeItemData;
use App\DTOs\PayrollRunIngestionData;
use App\Http\Requests\IngestPayrollRunRequest;
use App\Http\Resources\PayrollRunResource;
use App\Services\Accounting\PayrollIntegrationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IngestPayrollRunController extends Controller
{
    public function __invoke(
        IngestPayrollRunRequest $request,
        PayrollIntegrationService $service
    ): JsonResponse {
        $validated = $request->validated();

        $employees = array_map(
            fn (array $emp) => new PayrollEmployeeItemData(
                employeeIdNumber: (string) $emp['employee_id_number'],
                employeeName: (string) $emp['employee_name'],
                department: (string) $emp['department'],
                basicSalary: (string) $emp['basic_salary'],
                overtimePay: isset($emp['overtime_pay']) ? (string) $emp['overtime_pay'] : '0.0000',
                allowances: isset($emp['allowances']) ? (string) $emp['allowances'] : '0.0000',
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

        $payrollRun = $service->ingestAndDisbursePayroll($dto);

        return (new PayrollRunResource($payrollRun))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
