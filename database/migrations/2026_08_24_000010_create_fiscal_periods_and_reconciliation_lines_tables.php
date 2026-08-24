<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->string('period_code', 20)->unique(); // e.g. 2026-M01, 2026-Q1, 2026-FY
            $table->string('fiscal_year', 10)->index();
            $table->integer('period_number'); // 1 to 12
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->enum('status', ['OPEN', 'CLOSING_IN_PROGRESS', 'LOCKED', 'AUDITED'])->default('OPEN')->index();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('budget_encumbrances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_allocation_id')->constrained('budget_allocations')->restrictOnDelete();
            $table->string('reference_type', 50)->index(); // PO, PR, CONTRACT
            $table->string('reference_number', 50)->index();
            $table->decimal('encumbered_amount', 15, 4);
            $table->decimal('liquidated_amount', 15, 4)->default(0.0000);
            $table->enum('status', ['COMMITTED', 'LIQUIDATED', 'RELEASED'])->default('COMMITTED')->index();
            $table->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->date('transaction_date')->index();
            $table->string('reference_number', 100)->nullable();
            $table->string('description');
            $table->decimal('amount', 15, 4); // positive for deposit, negative for withdrawal
            $table->enum('match_status', ['MATCHED', 'UNMATCHED', 'OUTSTANDING_CHECK', 'DEPOSIT_IN_TRANSIT'])->default('UNMATCHED')->index();
            $table->foreignId('matched_journal_line_id')->nullable()->constrained('journal_entry_lines')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('budget_encumbrances');
        Schema::dropIfExists('fiscal_periods');
    }
};
