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
            if (! Schema::hasColumn('patient_accounts', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('full_name');
            }
            if (! Schema::hasColumn('patient_accounts', 'gender')) {
                $table->string('gender', 20)->nullable()->after('date_of_birth');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('patient_accounts', 'date_of_birth')) {
                $table->dropColumn(['date_of_birth', 'gender']);
            }
        });
    }
};
