<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('department')->index();
            $table->string('fiscal_year', 10)->index();
            $table->decimal('allocated_amount', 15, 4);
            $table->decimal('spent_amount', 15, 4)->default(0.0000);
            $table->decimal('remaining_balance', 15, 4);
            $table->string('status')->default('Approved')->index();
            $table->timestamps();
        });

        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->string('department');
            $table->string('payee_name');
            $table->decimal('amount', 15, 4);
            $table->string('purpose');
            $table->enum('status', ['PENDING', 'APPROVED', 'DISBURSED', 'REJECTED'])->default('PENDING')->index();
            $table->timestamps();
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->string('payer_name');
            $table->decimal('amount_paid', 15, 4);
            $table->string('payment_method')->index();
            $table->date('receipt_date')->index();
            $table->string('cashier_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('payment_requests');
        Schema::dropIfExists('budget_allocations');
    }
};
