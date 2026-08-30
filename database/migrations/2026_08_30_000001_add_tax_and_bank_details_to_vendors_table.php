<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (! Schema::hasColumn('vendors', 'tax_type')) {
                $table->string('tax_type', 30)->default('VAT_REGISTERED')->after('tin');
            }
            if (! Schema::hasColumn('vendors', 'default_ewt_rate')) {
                $table->decimal('default_ewt_rate', 15, 4)->default(1.0000)->after('tax_type');
            }
            if (! Schema::hasColumn('vendors', 'default_atc_code')) {
                $table->string('default_atc_code', 20)->default('WC158')->after('default_ewt_rate');
            }
            if (! Schema::hasColumn('vendors', 'registered_address')) {
                $table->text('registered_address')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('vendors', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('registered_address');
            }
            if (! Schema::hasColumn('vendors', 'bank_account_number')) {
                $table->string('bank_account_number', 100)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('vendors', 'bank_account_name')) {
                $table->string('bank_account_name', 255)->nullable()->after('bank_account_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $columnsToDrop = [
                'tax_type',
                'default_ewt_rate',
                'default_atc_code',
                'registered_address',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('vendors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
