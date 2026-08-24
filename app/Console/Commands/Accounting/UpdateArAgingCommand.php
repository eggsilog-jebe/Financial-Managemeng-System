<?php

declare(strict_types=1);

namespace App\Console\Commands\Accounting;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class UpdateArAgingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'accounting:update-ar-aging {--chunk=200 : Number of invoices per chunk}';

    /**
     * The console command description.
     */
    protected $description = 'Nightly batch computation to categorize unpaid hospital invoices into Current, 1-30, 31-60, 61-90, and 90+ days aging buckets using chunkById/lazy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Nightly Accounts Receivable Aging Categorization Batch...');

        $today = Carbon::today();
        $chunkSize = (int) $this->option('chunk');

        $metrics = [
            'current' => 0,
            '1_30'    => 0,
            '31_60'   => 0,
            '61_90'   => 0,
            '90_plus' => 0,
            'total'   => 0,
        ];

        $totalBalance = '0.0000';

        Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])
            ->chunkById($chunkSize, function ($invoices) use ($today, &$metrics, &$totalBalance): void {
                foreach ($invoices as $invoice) {
                    $days = $today->diffInDays(Carbon::parse($invoice->invoice_date));
                    $payable = (string) $invoice->patient_payable;

                    $totalBalance = bcadd($totalBalance, $payable, 4);
                    $metrics['total']++;

                    if ($days === 0) {
                        $metrics['current']++;
                    } elseif ($days <= 30) {
                        $metrics['1_30']++;
                    } elseif ($days <= 60) {
                        $metrics['31_60']++;
                    } elseif ($days <= 90) {
                        $metrics['61_90']++;
                    } else {
                        $metrics['90_plus']++;
                    }
                }
            });

        $this->table(
            ['Aging Bucket', 'Invoice Count', 'Percentage'],
            [
                ['Current (Day 0)', $metrics['current'], $metrics['total'] > 0 ? round(($metrics['current'] / $metrics['total']) * 100, 2) . '%' : '0%'],
                ['1 - 30 Days',     $metrics['1_30'],    $metrics['total'] > 0 ? round(($metrics['1_30'] / $metrics['total']) * 100, 2) . '%' : '0%'],
                ['31 - 60 Days',    $metrics['31_60'],   $metrics['total'] > 0 ? round(($metrics['31_60'] / $metrics['total']) * 100, 2) . '%' : '0%'],
                ['61 - 90 Days',    $metrics['61_90'],   $metrics['total'] > 0 ? round(($metrics['61_90'] / $metrics['total']) * 100, 2) . '%' : '0%'],
                ['90+ Days Past',   $metrics['90_plus'], $metrics['total'] > 0 ? round(($metrics['90_plus'] / $metrics['total']) * 100, 2) . '%' : '0%'],
            ]
        );

        $this->info("Successfully processed {$metrics['total']} open invoices with Total AR: ₱" . number_format((float) $totalBalance, 2));

        return Command::SUCCESS;
    }
}
