<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * High-performance B-Tree and composite covering indexes across critical financial tables.
     */
    public function up(): void
    {
        // 1. General Ledger: Account balance lookup & reporting covering indexes
        if (Schema::hasTable('journal_entry_lines')) {
            Schema::table('journal_entry_lines', function (Blueprint $table): void {
                // Standalone account_id lookup for COA balances and GL ledgers
                $table->index('account_id', 'idx_jel_account_id');
                // Covering index for account debit/credit aggregations (prevents table heap scan)
                $table->index(['account_id', 'journal_entry_id', 'debit', 'credit'], 'idx_jel_acc_entry_amounts');
            });
        }

        if (Schema::hasTable('journal_entries')) {
            Schema::table('journal_entries', function (Blueprint $table): void {
                // High-cardinality composite index for posted entry filtering and ledger reporting
                $table->index(['status', 'entry_date', 'id'], 'idx_je_status_date_id');
            });
        }

        // 2. Accounts Payable: Purchase bills & Aging schedules
        if (Schema::hasTable('purchase_bills')) {
            Schema::table('purchase_bills', function (Blueprint $table): void {
                // Vendor liability and balance lookup
                $table->index(['vendor_id', 'status'], 'idx_pb_vendor_status');
                // AP aging and open liability calculation covering index
                $table->index(['status', 'due_date', 'total_amount'], 'idx_pb_status_due_amount');
            });
        }

        // 3. Accounts Receivable: Patient invoices & Aging schedules
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table): void {
                // Patient balance and statement lookup
                $table->index(['patient_account_id', 'status'], 'idx_inv_patient_status');
                // AR aging and open balance calculation covering index
                $table->index(['status', 'invoice_date', 'patient_payable'], 'idx_inv_status_date_payable');
            });
        }

        // 4. Session Security & High-Frequency Request Gatekeeping
        if (Schema::hasTable('user_active_sessions')) {
            Schema::table('user_active_sessions', function (Blueprint $table): void {
                // Critical: Single Active Session middleware runs this on EVERY request
                $table->index(['user_id', 'is_terminated', 'login_at'], 'idx_uas_user_active_login');
                $table->index(['session_id', 'is_terminated'], 'idx_uas_session_active');
            });
        }

        // 5. Immutable Audit Logs & Telemetry
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table): void {
                // Daily KPI telemetry (logins, mutations today)
                $table->index(['event', 'created_at'], 'idx_act_event_created');
                // Log viewer filtering
                $table->index(['module', 'event', 'created_at'], 'idx_act_mod_ev_created');
            });
        }

        // 6. Cashier Collections & Banking Intact Compliance
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->index(['payment_method', 'amount'], 'idx_pay_method_amount');
                $table->index(['patient_account_id', 'payment_date'], 'idx_pay_patient_date');
            });
        }

        if (Schema::hasTable('bank_deposits')) {
            Schema::table('bank_deposits', function (Blueprint $table): void {
                $table->index(['status', 'total_deposited'], 'idx_bd_status_deposited');
            });
        }

        // 7. Malasakit & PhilHealth Public Assistance
        if (Schema::hasTable('guarantee_letters')) {
            Schema::table('guarantee_letters', function (Blueprint $table): void {
                $table->index(['patient_account_id', 'status'], 'idx_gl_patient_status');
                $table->index(['status', 'authorized_amount', 'utilized_amount'], 'idx_gl_status_amounts');
            });
        }

        if (Schema::hasTable('philhealth_claims')) {
            Schema::table('philhealth_claims', function (Blueprint $table): void {
                $table->index(['claim_status', 'total_case_rate_amount'], 'idx_phc_status_rate');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('philhealth_claims')) {
            Schema::table('philhealth_claims', function (Blueprint $table): void {
                $table->dropIndex('idx_phc_status_rate');
            });
        }

        if (Schema::hasTable('guarantee_letters')) {
            Schema::table('guarantee_letters', function (Blueprint $table): void {
                $table->dropIndex('idx_gl_patient_status');
                $table->dropIndex('idx_gl_status_amounts');
            });
        }

        if (Schema::hasTable('bank_deposits')) {
            Schema::table('bank_deposits', function (Blueprint $table): void {
                $table->dropIndex('idx_bd_status_deposited');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropIndex('idx_pay_method_amount');
                $table->dropIndex('idx_pay_patient_date');
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table): void {
                $table->dropIndex('idx_act_event_created');
                $table->dropIndex('idx_act_mod_ev_created');
            });
        }

        if (Schema::hasTable('user_active_sessions')) {
            Schema::table('user_active_sessions', function (Blueprint $table): void {
                $table->dropIndex('idx_uas_user_active_login');
                $table->dropIndex('idx_uas_session_active');
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table): void {
                $table->dropIndex('idx_inv_patient_status');
                $table->dropIndex('idx_inv_status_date_payable');
            });
        }

        if (Schema::hasTable('purchase_bills')) {
            Schema::table('purchase_bills', function (Blueprint $table): void {
                $table->dropIndex('idx_pb_vendor_status');
                $table->dropIndex('idx_pb_status_due_amount');
            });
        }

        if (Schema::hasTable('journal_entries')) {
            Schema::table('journal_entries', function (Blueprint $table): void {
                $table->dropIndex('idx_je_status_date_id');
            });
        }

        if (Schema::hasTable('journal_entry_lines')) {
            Schema::table('journal_entry_lines', function (Blueprint $table): void {
                $table->dropIndex('idx_jel_account_id');
                $table->dropIndex('idx_jel_acc_entry_amounts');
            });
        }
    }
};
