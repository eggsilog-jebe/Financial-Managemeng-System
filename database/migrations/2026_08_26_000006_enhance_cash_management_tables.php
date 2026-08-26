<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('bank_accounts', 'gl_account_id')) {
                $table->foreignId('gl_account_id')->nullable()->after('gl_code')->constrained('accounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('bank_accounts', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 4)->default(0.0000)->after('currency');
            }
            if (! Schema::hasColumn('bank_accounts', 'minimum_balance')) {
                $table->decimal('minimum_balance', 15, 4)->default(50000.0000)->after('balance');
            }
            if (! Schema::hasColumn('bank_accounts', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status')->index();
            }
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            if (! Schema::hasColumn('fund_transfers', 'source_bank_account_id')) {
                $table->foreignId('source_bank_account_id')->nullable()->after('id')->constrained('bank_accounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('fund_transfers', 'destination_bank_account_id')) {
                $table->foreignId('destination_bank_account_id')->nullable()->after('source_bank_account_id')->constrained('bank_accounts')->nullOnDelete();
            }
            if (! Schema::hasColumn('fund_transfers', 'journal_entry_id')) {
                $table->foreignId('journal_entry_id')->nullable()->after('destination_number')->constrained('journal_entries')->nullOnDelete();
            }
            if (! Schema::hasColumn('fund_transfers', 'memo')) {
                $table->text('memo')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('fund_transfers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('bank_reconciliations', function (Blueprint $table) {
            if (! Schema::hasColumn('bank_reconciliations', 'cutoff_date')) {
                $table->date('cutoff_date')->nullable()->after('statement_date')->index();
            }
            if (! Schema::hasColumn('bank_reconciliations', 'cleared_deposits')) {
                $table->decimal('cleared_deposits', 15, 4)->default(0.0000)->after('variance');
            }
            if (! Schema::hasColumn('bank_reconciliations', 'cleared_disbursements')) {
                $table->decimal('cleared_disbursements', 15, 4)->default(0.0000)->after('cleared_deposits');
            }
            if (! Schema::hasColumn('bank_reconciliations', 'reconciled_by')) {
                $table->foreignId('reconciled_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('bank_reconciliations', 'notes')) {
                $table->text('notes')->nullable()->after('reconciled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_reconciliations', function (Blueprint $table) {
            $table->dropForeign(['reconciled_by']);
            $table->dropColumn(['cutoff_date', 'cleared_deposits', 'cleared_disbursements', 'reconciled_by', 'notes']);
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropForeign(['source_bank_account_id']);
            $table->dropForeign(['destination_bank_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['source_bank_account_id', 'destination_bank_account_id', 'journal_entry_id', 'memo', 'created_by']);
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['gl_account_id']);
            $table->dropColumn(['gl_account_id', 'opening_balance', 'minimum_balance', 'is_active']);
        });
    }
};
