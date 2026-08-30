<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * G-05 Remediation: Fix tax_rules.rate column from decimal(5,2) to decimal(7,4).
 *
 * The original decimal(5,2) only supports values like 12.00 (VAT) but cannot
 * store BIR EWT rates such as 0.0100 (1% goods), 0.0200 (2% services),
 * 0.1000 (10% medical PF), or 0.1500 (15% medical PF) with full precision.
 *
 * decimal(7,4) supports values from 0.0001 up to 999.9999, covering all
 * Philippine BIR tax rates including fractional EWT rates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_rules', function (Blueprint $table) {
            // Widen rate column: decimal(5,2) -> decimal(7,4)
            // Allows fractional EWT rates (e.g. 0.0100 for 1%, 0.1500 for 15%)
            // while still supporting whole-number VAT rates (e.g. 12.0000)
            $table->decimal('rate', 7, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tax_rules', function (Blueprint $table) {
            $table->decimal('rate', 5, 2)->change();
        });
    }
};
