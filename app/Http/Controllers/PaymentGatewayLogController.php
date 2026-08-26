<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Accounting\BankDepositService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentGatewayLogController extends Controller
{
    public function __construct(
        private readonly BankDepositService $bankDepositService
    ) {}

    public function index(): View
    {
        $logs = Payment::with(['patientAccount', 'invoice', 'officialReceipt', 'cashierShift'])
            ->where(function ($q) {
                $q->where('payment_method', '!=', 'CASH')
                  ->orWhereNotNull('transaction_channel_ref');
            })
            ->latest('payment_date')
            ->get();

        // Match posted Journal Entries by payment reference pattern
        $refs = $logs->pluck('payment_reference')->filter()->values()->all();
        $journalEntries = \App\Models\JournalEntry::where(function ($q) use ($refs) {
            foreach ($refs as $ref) {
                $q->orWhere('reference_number', 'LIKE', "%{$ref}%");
            }
        })->get()->keyBy(function ($je) {
            // Map by payment ref contained in JE reference number
            foreach (['JE-COL-', 'JE-COL-SYNC-'] as $prefix) {
                if (str_starts_with($je->reference_number, $prefix)) {
                    $cleaned = str_replace($prefix, '', $je->reference_number);
                    // if has suffix random bytes e.g. -a1b2
                    return explode('-', $cleaned)[0] ?? $cleaned;
                }
            }
            return $je->reference_number;
        });

        foreach ($logs as $log) {
            $matchingJe = $journalEntries->first(function ($je) use ($log) {
                return str_contains($je->reference_number, (string) $log->payment_reference);
            });
            $log->journalEntry = $matchingJe;
        }

        $gateways = $logs;
        $totalOnline = $logs->sum('amount');

        $viewName = view()->exists('accounting.collection.gateway-logs.index')
            ? 'accounting.collection.gateway-logs.index'
            : 'collection.payment-gateway-logs';

        return view($viewName, compact('logs', 'gateways', 'totalOnline'));
    }

    public function retriggerGl(int $id): RedirectResponse
    {
        try {
            $je = $this->bankDepositService->retriggerPaymentJournal($id);

            return redirect()->back()->with('success', "Double-entry journal [{$je->reference_number}] posted successfully for Payment #{$id}.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
