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
            if (! Schema::hasColumn('patient_accounts', 'discount_category')) {
                $table->string('discount_category', 50)->default('NONE')->after('admission_type')->index();
            }
            if (! Schema::hasColumn('patient_accounts', 'id_card_number')) {
                $table->string('id_card_number', 50)->nullable()->after('discount_category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('patient_accounts', 'id_card_number')) {
                $table->dropColumn(['discount_category', 'id_card_number']);
            }
        });
    }
};
