<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('tin', 30)->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 50)->unique();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->date('bill_date')->index();
            $table->date('due_date')->index();
            $table->decimal('total_amount', 15, 4);
            $table->decimal('paid_amount', 15, 4)->default(0.0000);
            $table->enum('status', ['UNPAID', 'PARTIAL', 'PAID', 'OVERDUE'])->default('UNPAID')->index();
            $table->timestamps();
        });

        Schema::create('patient_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id_number', 30)->unique();
            $table->string('full_name');
            $table->string('admission_type')->default('Inpatient')->index();
            $table->string('hmo_provider')->nullable()->index();
            $table->decimal('total_billed', 15, 4)->default(0.0000);
            $table->decimal('current_balance', 15, 4)->default(0.0000);
            $table->string('status')->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('patient_account_id')->constrained('patient_accounts')->restrictOnDelete();
            $table->date('invoice_date')->index();
            $table->decimal('total_amount', 15, 4);
            $table->decimal('insurance_covered', 15, 4)->default(0.0000);
            $table->decimal('patient_payable', 15, 4);
            $table->enum('status', ['UNPAID', 'PARTIAL', 'SETTLED'])->default('UNPAID')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('patient_accounts');
        Schema::dropIfExists('purchase_bills');
        Schema::dropIfExists('vendors');
    }
};
