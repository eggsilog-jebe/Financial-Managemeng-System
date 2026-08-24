<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('item_code', 50)->index();
            $table->string('description');
            $table->string('department', 50)->index(); // LIS, RIS, PHARMACY, ROOM, SURGERY
            $table->string('revenue_category', 50)->default('CLINICAL')->index(); // CLINICAL, PHARMACY, ROOM, DOCTOR_FEE
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('gross_amount', 15, 4);
            $table->boolean('is_vatable')->default(true);
            $table->boolean('is_senior_pwd_eligible')->default(true);
            $table->timestamps();
        });

        Schema::create('philhealth_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->string('claim_series_number', 50)->unique();
            $table->string('member_pin', 30)->nullable();
            $table->string('patient_pin', 30)->nullable();
            $table->string('membership_type', 50)->default('EMPLOYED');
            $table->string('primary_icd_code', 20)->nullable();
            $table->string('primary_case_rate_code', 30)->nullable();
            $table->decimal('primary_case_rate_amount', 15, 4)->default(0.0000);
            $table->string('secondary_case_rate_code', 30)->nullable();
            $table->decimal('secondary_case_rate_amount', 15, 4)->default(0.0000);
            $table->decimal('total_case_rate_amount', 15, 4)->default(0.0000);
            $table->decimal('hospital_fee_share', 15, 4)->default(0.0000);
            $table->decimal('professional_fee_share', 15, 4)->default(0.0000);
            $table->enum('claim_status', ['DRAFT', 'TRANSMITTED', 'IN_PROCESS', 'APPROVED', 'PAID', 'DENIED', 'RTH'])->default('DRAFT')->index();
            $table->date('transmitted_at')->nullable();
            $table->date('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hmo_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->string('hmo_provider', 100)->index();
            $table->string('loa_number', 50)->nullable(); // Letter of Authorization
            $table->string('card_number', 50)->nullable();
            $table->decimal('approved_limit', 15, 4)->default(0.0000);
            $table->decimal('claimed_amount', 15, 4);
            $table->decimal('settled_amount', 15, 4)->default(0.0000);
            $table->enum('status', ['PENDING_BILLING', 'SUBMITTED', 'APPROVED', 'SETTLED', 'DISPUTED'])->default('PENDING_BILLING')->index();
            $table->timestamps();
        });

        Schema::create('statutory_discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->enum('discount_type', ['SENIOR_CITIZEN', 'PWD', 'EMPLOYEE', 'CHARITY'])->default('SENIOR_CITIZEN')->index();
            $table->string('id_card_number', 50)->nullable(); // OSCA / PWD ID
            $table->decimal('vat_exempt_amount', 15, 4)->default(0.0000); // 12% VAT relief removed from gross
            $table->decimal('discount_rate', 5, 4)->default(0.2000); // 20% standard
            $table->decimal('discount_amount', 15, 4);
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number', 50)->unique();
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->foreignId('patient_account_id')->constrained('patient_accounts')->restrictOnDelete();
            $table->date('issue_date')->index();
            $table->decimal('amount', 15, 4);
            $table->string('reason');
            $table->enum('status', ['DRAFT', 'APPROVED', 'APPLIED', 'VOID'])->default('APPROVED')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('statutory_discounts');
        Schema::dropIfExists('hmo_claims');
        Schema::dropIfExists('philhealth_claims');
        Schema::dropIfExists('invoice_items');
    }
};
