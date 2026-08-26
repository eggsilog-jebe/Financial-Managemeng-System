<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collection;

use App\DTOs\Accounting\CashierShiftCloseData;
use App\DTOs\Accounting\CashierShiftOpenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\CloseShiftRequest;
use App\Http\Requests\Collection\OpenShiftRequest;
use App\Models\CashierShift;
use App\Services\Accounting\CashierPaymentService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CashierShiftController extends Controller
{
    public function __construct(
        private readonly CashierPaymentService $cashierPaymentService
    ) {}

    public function open(OpenShiftRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $cashierId = auth()->id() ?? 1;

        try {
            $dto = new CashierShiftOpenData(
                cashierId: $cashierId,
                terminalName: $validated['terminal_name'],
                openingCashFloat: (string) $validated['opening_cash_float']
            );

            $shift = $this->cashierPaymentService->openShift($dto);

            return redirect()->back()->with('success', "Shift [{$shift->shift_code}] opened successfully on {$shift->terminal_name} with float ₱" . number_format((float) $shift->opening_cash_float, 2));
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function close(CloseShiftRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $shiftId = ! empty($validated['shift_id']) 
            ? (int) $validated['shift_id'] 
            : (CashierShift::where('status', 'OPEN')->latest('opened_at')->first()?->id);

        if (! $shiftId) {
            return redirect()->back()->with('error', 'No active open shift found to close.');
        }

        try {
            $dto = new CashierShiftCloseData(
                actualCashCounted: (string) $validated['actual_cash_counted'],
                varianceReason: $validated['variance_reason'] ?? null
            );

            $shift = $this->cashierPaymentService->closeShift($shiftId, $dto);

            return redirect()->back()
                ->with('success', "Shift [{$shift->shift_code}] closed successfully! Counted Cash: ₱" . number_format((float) $shift->actual_cash_counted, 2) . " | Variance: ₱" . number_format((float) $shift->cash_variance, 2))
                ->with('turnover_summary', [
                    'shift_code'          => $shift->shift_code,
                    'terminal_name'       => $shift->terminal_name,
                    'cashier_name'        => $shift->cashier?->name ?? 'Cashier Officer',
                    'opened_at'           => $shift->opened_at?->format('M d, Y h:i A') ?? '-',
                    'closed_at'           => $shift->closed_at?->format('M d, Y h:i A') ?? '-',
                    'opening_float'       => number_format((float) $shift->opening_cash_float, 2),
                    'expected_cash'       => number_format((float) $shift->expected_cash, 2),
                    'actual_cash'         => number_format((float) $shift->actual_cash_counted, 2),
                    'cash_variance'       => number_format((float) $shift->cash_variance, 2),
                    'digital_collections' => number_format((float) $shift->total_digital_collections, 2),
                    'total_collections'   => number_format((float) $shift->total_collections, 2),
                    'variance_reason'     => $dto->varianceReason ?? 'N/A (Standard Turnover)',
                ]);
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reconcile(int $id): RedirectResponse
    {
        $supervisorId = auth()->id() ?? 1;

        try {
            $shift = $this->cashierPaymentService->reconcileShift($id, $supervisorId);

            return redirect()->back()->with('success', "Shift [{$shift->shift_code}] has been verified and reconciled.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
