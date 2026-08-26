<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_accounts', 'phone')) {
                $table->string('phone', 50)->nullable()->after('hmo_provider');
            }
            if (! Schema::hasColumn('patient_accounts', 'email')) {
                $table->string('email', 100)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('patient_accounts', 'address')) {
                $table->string('address', 255)->nullable()->after('email');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('invoice_date');
            }
            if (! Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 4)->default(0.0000)->after('insurance_covered');
            }
            if (! Schema::hasColumn('invoices', 'vat_amount')) {
                $table->decimal('vat_amount', 15, 4)->default(0.0000)->after('discount_amount');
            }
            if (! Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 4)->default(0.0000)->after('patient_payable');
            }
            $table->string('status', 30)->default('UNPAID')->change();
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('credit_notes', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            }
            $table->string('status', 30)->default('APPROVED')->change();
        });
    }

    public function down(): void
    {
        Schema::table('credit_notes', function (Blueprint $table) {
            if (Schema::hasColumn('credit_notes', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'due_date')) {
                $table->dropColumn(['due_date', 'discount_amount', 'vat_amount', 'paid_amount']);
            }
        });

        Schema::table('patient_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('patient_accounts', 'phone')) {
                $table->dropColumn(['phone', 'email', 'address']);
            }
        });
    }
};
