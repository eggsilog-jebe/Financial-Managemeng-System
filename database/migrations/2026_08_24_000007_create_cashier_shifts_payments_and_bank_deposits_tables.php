<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_code', 30)->unique();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
            $table->string('terminal_name', 50)->default('POS-MAIN-01');
            $table->timestamp('opened_at')->index();
            $table->timestamp('closed_at')->nullable()->index();
            $table->decimal('opening_cash_float', 15, 4)->default(0.0000);
            $table->decimal('expected_cash', 15, 4)->default(0.0000);
            $table->decimal('actual_cash_counted', 15, 4)->default(0.0000);
            $table->decimal('cash_variance', 15, 4)->default(0.0000);
            $table->decimal('total_digital_collections', 15, 4)->default(0.0000);
            $table->decimal('total_collections', 15, 4)->default(0.0000);
            $table->enum('status', ['OPEN', 'BALANCING', 'CLOSED', 'RECONCILED'])->default('OPEN')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference', 50)->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('patient_account_id')->constrained('patient_accounts')->restrictOnDelete();
            $table->foreignId('cashier_shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->date('payment_date')->index();
            $table->decimal('amount', 15, 4);
            $table->enum('payment_method', ['CASH', 'CREDIT_CARD', 'DEBIT_CARD', 'QR_PH', 'GCASH', 'MAYA', 'CHECK', 'ONLINE_BANK', 'BANK_TRANSFER', 'SPLIT_PAYMENT'])->default('CASH')->index();
            $table->string('transaction_channel_ref', 100)->nullable(); // POS Auth Code / GCash Reference
            $table->enum('payment_type', ['PATIENT_COPAY', 'ADMISSION_DEPOSIT', 'HMO_SETTLEMENT', 'PHILHEALTH_SETTLEMENT'])->default('PATIENT_COPAY')->index();
            $table->timestamps();
        });

        Schema::create('official_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('or_number', 50)->unique(); // BIR Official Receipt sequential number
            $table->foreignId('payment_id')->constrained('payments')->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('patient_account_id')->constrained('patient_accounts')->restrictOnDelete();
            $table->date('or_date')->index();
            $table->string('payor_name');
            $table->string('payor_tin', 30)->nullable();
            $table->decimal('vatable_sales', 15, 4)->default(0.0000);
            $table->decimal('vat_exempt_sales', 15, 4)->default(0.0000);
            $table->decimal('zero_rated_sales', 15, 4)->default(0.0000);
            $table->decimal('vat_amount', 15, 4)->default(0.0000); // 12% Output VAT
            $table->decimal('total_amount_collected', 15, 4);
            $table->enum('status', ['VALID', 'CANCELLED'])->default('VALID')->index();
            $table->timestamps();
        });

        Schema::create('bank_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_reference', 50)->unique();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('cashier_shift_id')->nullable()->constrained('cashier_shifts')->nullOnDelete();
            $table->date('deposit_date')->index();
            $table->decimal('cash_amount', 15, 4)->default(0.0000);
            $table->decimal('check_amount', 15, 4)->default(0.0000);
            $table->decimal('total_deposited', 15, 4);
            $table->string('bank_reference_number', 100)->nullable();
            $table->string('validated_by_teller', 100)->nullable();
            $table->enum('status', ['PREPARED', 'IN_TRANSIT', 'DEPOSITED', 'RECONCILED'])->default('PREPARED')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_deposits');
        Schema::dropIfExists('official_receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('cashier_shifts');
    }
};
