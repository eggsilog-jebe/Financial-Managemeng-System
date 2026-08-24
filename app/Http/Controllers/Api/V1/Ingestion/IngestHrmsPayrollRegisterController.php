<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ingestion;

use App\DTOs\PayrollEmployeeItemData;
use App\DTOs\PayrollRunIngestionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IngestHrmsPayrollRegisterRequest;
use App\Http\Resources\PayrollRunResource;
use App\Services\Accounting\PayrollIntegrationService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class IngestHrmsPayrollRegisterController extends Controller
{
    /**
     * Ingest hospital staff payroll register from HRMS subsystem.
     * Route: POST /api/v1/ingest/payroll-register
     */
    public function __invoke(
        IngestHrmsPayrollRegisterRequest $request,
        PayrollIntegrationService $service
    ): JsonResponse {
        $validated = $request->validated();

        $employeeList = $validated['employees'] ?? [];

        if (empty($employeeList)) {
            // Summary level ingestion fallback
            $employeeList = [
                [
                    'employee_id_number' => 'HRMS-BATCH-SUMMARY',
                    'employee_name'      => 'Hospital Staff Batch Payroll (HRMS Ingestion)',
                    'department'         => 'HOSPITAL_OPERATIONS',
                    'basic_salary'       => (string) $validated['total_gross_pay'],
                ]
            ];
        }

        $employees = array_map(
            fn (array $emp) => new PayrollEmployeeItemData(
                employeeIdNumber: (string) $emp['employee_id_number'],
                employeeName: (string) $emp['employee_name'],
                department: (string) $emp['department'],
                basicSalary: (string) $emp['basic_salary'],
                overtimePay: '0.0000',
                allowances: '0.0000',
            ),
            $employeeList
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
