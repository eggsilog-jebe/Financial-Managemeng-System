<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disbursement_vouchers', function (Blueprint $table) {
            if (! Schema::hasColumn('disbursement_vouchers', 'prepared_by')) {
                $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete()->after('bank_account_id');
            }
            if (! Schema::hasColumn('disbursement_vouchers', 'audited_by')) {
                $table->foreignId('audited_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_by');
            }
            if (! Schema::hasColumn('disbursement_vouchers', 'audited_at')) {
                $table->timestamp('audited_at')->nullable()->after('audited_by');
            }
            if (! Schema::hasColumn('disbursement_vouchers', 'description')) {
                $table->string('description')->nullable()->after('payee_name');
            }
            $table->string('status', 30)->default('PREPARED')->change();
        });

        Schema::table('check_registers', function (Blueprint $table) {
            $table->string('status', 30)->default('ISSUED')->change();
        });

        if (! Schema::hasTable('petty_cash_funds')) {
            Schema::create('petty_cash_funds', function (Blueprint $table) {
                $table->id();
                $table->string('fund_name');
                $table->string('custodian_name');
                $table->decimal('float_limit', 15, 4)->default(50000.0000);
                $table->decimal('current_balance', 15, 4)->default(50000.0000);
                $table->string('gl_code', 30)->default('1030')->index();
                $table->string('status', 30)->default('Active')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('petty_cash_expenses')) {
            Schema::create('petty_cash_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('petty_cash_fund_id')->constrained('petty_cash_funds')->cascadeOnDelete();
                $table->string('voucher_number', 50)->unique();
                $table->date('expense_date')->index();
                $table->string('payee');
                $table->string('department')->default('ADMIN');
                $table->string('particulars');
                $table->decimal('amount', 15, 4);
                $table->string('receipt_ref')->nullable();
                $table->foreignId('disbursement_voucher_id')->nullable()->constrained('disbursement_vouchers')->nullOnDelete();
                $table->string('status', 30)->default('UNREPLENISHED')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_expenses');
        Schema::dropIfExists('petty_cash_funds');

        Schema::table('disbursement_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('disbursement_vouchers', 'prepared_by')) {
                $table->dropForeign(['prepared_by']);
                $table->dropColumn('prepared_by');
            }
            if (Schema::hasColumn('disbursement_vouchers', 'audited_by')) {
                $table->dropForeign(['audited_by']);
                $table->dropColumn(['audited_by', 'audited_at', 'description']);
            }
        });
    }
};
