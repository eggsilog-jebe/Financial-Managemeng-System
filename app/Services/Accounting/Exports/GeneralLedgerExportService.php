<?php

declare(strict_types=1);

namespace App\Services\Accounting\Exports;

use App\Models\JournalEntryLine;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GeneralLedgerExportService
{
    /**
     * Memory-safe export of all General Ledger transaction lines using chunkById / lazy for BIR CAS audits.
     */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'General_Ledger_Book_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function (): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fputs($handle, "\xEF\xBB\xBF");

            // Header
            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['General Ledger Book & Transaction Register']);
            fputcsv($handle, ['Exported: ' . date('Y-m-d H:i:s')]);
            fputcsv($handle, ['BIR CAS Audit Trail File']);
            fputcsv($handle, []);

            // Columns
            fputcsv($handle, [
                'Line ID',
                'Posting Date',
                'Journal Ref #',
                'Journal Description',
                'Entry Type',
                'Status',
                'Account Code',
                'Account Name',
                'Department',
                'Debit (PHP)',
                'Credit (PHP)',
                'Line Memo',
            ]);

            // Stream chunk-by-chunk using cursor/lazy to prevent memory overflow
            JournalEntryLine::with(['journalEntry', 'account'])
                ->whereHas('journalEntry', function ($q) {
                    $q->where('status', 'POSTED');
                })
                ->orderBy('id')
                ->lazy(500)
                ->each(function (JournalEntryLine $line) use ($handle): void {
                    fputcsv($handle, [
                        $line->id,
                        $line->journalEntry->entry_date->format('Y-m-d'),
                        $line->journalEntry->reference_number,
                        $line->journalEntry->description,
                        $line->journalEntry->type,
                        $line->journalEntry->status,
                        $line->account->code,
                        $line->account->name,
                        $line->account->department ?? 'FINANCE',
                        number_format((float) $line->debit, 2, '.', ''),
                        number_format((float) $line->credit, 2, '.', ''),
                        $line->memo ?? '',
                    ]);
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
