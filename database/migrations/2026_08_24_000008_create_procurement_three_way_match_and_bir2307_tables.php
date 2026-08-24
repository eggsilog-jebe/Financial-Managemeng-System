<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_code', 30)->unique();
            $table->string('full_name');
            $table->string('tin', 30)->index();
            $table->string('specialization')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('ewt_rate_type', ['INDIVIDUAL_BELOW_3M', 'INDIVIDUAL_ABOVE_3M', 'CORPORATE'])->default('INDIVIDUAL_BELOW_3M')->index();
            $table->decimal('default_ewt_rate', 5, 4)->default(0.1000); // 10% or 15% (or 5% if sworn declaration submitted)
            $table->boolean('has_sworn_declaration')->default(false);
            $table->string('status')->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->cascadeOnDelete();
            $table->string('item_code', 50)->index();
            $table->string('description');
            $table->enum('expense_type', ['GOODS_INVENTORY', 'SERVICES_MAINTENANCE', 'DOCTOR_PROFESSIONAL_FEE', 'CAPEX_EQUIPMENT', 'UTILITIES'])->default('GOODS_INVENTORY')->index();
            $table->decimal('quantity', 10, 2)->default(1.00);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('gross_amount', 15, 4);
            $table->string('atc_code', 20)->default('WI158'); // BIR ATC: WI158 (goods 1%), WI160 (services 2%), WI010/WI020 (medical PF 10%/15%)
            $table->decimal('ewt_rate', 5, 4)->default(0.0100);
            $table->decimal('ewt_amount', 15, 4)->default(0.0000);
            $table->decimal('net_payable', 15, 4);
            $table->timestamps();
        });

        Schema::create('three_way_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->restrictOnDelete();
            $table->string('po_number', 50)->index(); // Purchase Order (PSM)
            $table->string('grn_number', 50)->index(); // Goods Received Note (SWS)
            $table->string('vendor_invoice_number', 50)->index(); // BIR Sales Invoice
            $table->decimal('po_amount', 15, 4);
            $table->decimal('grn_amount', 15, 4);
            $table->decimal('invoice_amount', 15, 4);
            $table->decimal('price_variance', 15, 4)->default(0.0000);
            $table->decimal('quantity_variance', 10, 2)->default(0.00);
            $table->enum('match_status', ['MATCHED', 'PRICE_MISMATCH', 'QTY_MISMATCH', 'OVER_BILLED', 'PENDING'])->default('MATCHED')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bir_2307_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number', 50)->unique();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->restrictOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('doctor_profiles')->nullOnDelete();
            $table->date('period_from')->index();
            $table->date('period_to')->index();
            $table->string('payee_name');
            $table->string('payee_tin', 30);
            $table->string('atc_code', 20)->index();
            $table->decimal('tax_base_amount', 15, 4);
            $table->decimal('tax_rate', 5, 4);
            $table->decimal('tax_withheld', 15, 4);
            $table->string('form_status')->default('GENERATED')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bir_2307_certificates');
        Schema::dropIfExists('three_way_matches');
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('doctor_profiles');
    }
};
