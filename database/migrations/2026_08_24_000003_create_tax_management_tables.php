<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->string('tax_code', 40)->unique();
            $table->string('name');
            $table->string('atc_code', 30)->index();
            $table->string('category')->index();
            $table->string('cat_type', 30)->index();
            $table->decimal('rate', 5, 2);
            $table->text('scope')->nullable();
            $table->string('status')->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('tax_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('cert_number', 50)->unique();
            $table->string('payee_name');
            $table->string('payee_role')->nullable();
            $table->string('payee_type')->default('doctor')->index();
            $table->string('tin', 30);
            $table->string('atc_code', 30);
            $table->decimal('gross_income', 15, 4);
            $table->decimal('tax_withheld', 15, 4);
            $table->string('form_type', 20)->default('2307')->index();
            $table->timestamps();
        });

        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            $table->string('form_type', 20)->index();
            $table->string('period_covered');
            $table->decimal('tax_due', 15, 4);
            $table->enum('status', ['DRAFT', 'FILED', 'PAID'])->default('DRAFT')->index();
            $table->date('filing_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_returns');
        Schema::dropIfExists('tax_certificates');
        Schema::dropIfExists('tax_rules');
    }
};
