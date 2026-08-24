<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_run_number', 50)->unique();
            $table->date('cutoff_start')->index();
            $table->date('cutoff_end')->index();
            $table->date('payout_date')->index();
            $table->integer('employee_count')->default(0);
            $table->decimal('total_gross_pay', 15, 4);
            $table->decimal('total_sss_employee', 15, 4)->default(0.0000);
            $table->decimal('total_sss_employer', 15, 4)->default(0.0000);
            $table->decimal('total_philhealth_employee', 15, 4)->default(0.0000);
            $table->decimal('total_philhealth_employer', 15, 4)->default(0.0000);
            $table->decimal('total_pagibig_employee', 15, 4)->default(0.0000);
            $table->decimal('total_pagibig_employer', 15, 4)->default(0.0000);
            $table->decimal('total_withholding_tax_1601c', 15, 4)->default(0.0000); // BIR 1601-C
            $table->decimal('total_statutory_deductions', 15, 4)->default(0.0000);
            $table->decimal('total_net_pay', 15, 4);
            $table->enum('status', ['DRAFT', 'AUDITED', 'APPROVED', 'DISBURSED'])->default('APPROVED')->index();
            $table->timestamps();
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->string('employee_id_number', 50)->index();
            $table->string('employee_name');
            $table->string('department', 50)->index();
            $table->string('tin', 30)->nullable();
            $table->string('sss_number', 30)->nullable();
            $table->string('philhealth_number', 30)->nullable();
            $table->string('pagibig_number', 30)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->decimal('basic_salary', 15, 4);
            $table->decimal('overtime_pay', 15, 4)->default(0.0000);
            $table->decimal('allowances', 15, 4)->default(0.0000);
            $table->decimal('gross_pay', 15, 4);
            $table->decimal('sss_employee_share', 15, 4)->default(0.0000);
            $table->decimal('sss_employer_share', 15, 4)->default(0.0000);
            $table->decimal('philhealth_employee_share', 15, 4)->default(0.0000);
            $table->decimal('philhealth_employer_share', 15, 4)->default(0.0000);
            $table->decimal('pagibig_employee_share', 15, 4)->default(0.0000);
            $table->decimal('pagibig_employer_share', 15, 4)->default(0.0000);
            $table->decimal('withholding_tax', 15, 4)->default(0.0000); // 1601-C
            $table->decimal('net_pay', 15, 4);
            $table->timestamps();
        });

        Schema::create('disbursement_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique();
            $table->foreignId('purchase_bill_id')->nullable()->constrained('purchase_bills')->nullOnDelete();
            $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->date('voucher_date')->index();
            $table->string('payee_name');
            $table->decimal('gross_amount', 15, 4);
            $table->decimal('withheld_tax_amount', 15, 4)->default(0.0000);
            $table->decimal('net_disbursed_amount', 15, 4);
            $table->enum('payment_method', ['CHECK', 'PESONET_EFT', 'INSTAPAY', 'PETTY_CASH', 'TELEGRAPHIC_TRANSFER'])->default('PESONET_EFT')->index();
            $table->string('check_or_eft_ref', 50)->nullable();
            $table->enum('status', ['DRAFT', 'AUDITED', 'APPROVED', 'RELEASED', 'CLEARED', 'CANCELLED'])->default('APPROVED')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        Schema::create('check_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disbursement_voucher_id')->constrained('disbursement_vouchers')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->string('check_number', 50)->unique();
            $table->date('check_date')->index();
            $table->string('payee_name');
            $table->decimal('amount', 15, 4);
            $table->enum('status', ['ISSUED', 'PRINTED', 'RELEASED', 'CLEARED', 'STALE', 'VOID'])->default('ISSUED')->index();
            $table->date('cleared_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_registers');
        Schema::dropIfExists('disbursement_vouchers');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
    }
};
