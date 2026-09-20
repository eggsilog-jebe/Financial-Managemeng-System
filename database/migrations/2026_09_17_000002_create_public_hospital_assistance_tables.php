<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add public hospital classification and NBB fields to patient_accounts
        Schema::table('patient_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_accounts', 'patient_type')) {
                $table->string('patient_type', 40)->default('paying')->index()->after('admission_type');
            }
            if (! Schema::hasColumn('patient_accounts', 'is_nbb')) {
                $table->boolean('is_nbb')->default(false)->index()->after('patient_type');
            }
            if (! Schema::hasColumn('patient_accounts', 'philhealth_member_type')) {
                $table->string('philhealth_member_type', 50)->nullable()->after('is_nbb');
            }
            if (! Schema::hasColumn('patient_accounts', 'philhealth_id_number')) {
                $table->string('philhealth_id_number', 50)->nullable()->after('philhealth_member_type');
            }
        });

        // 2. Create guarantee_letters table for PCSO, DSWD, DOH-MAIP subsidies
        if (! Schema::hasTable('guarantee_letters')) {
            Schema::create('guarantee_letters', function (Blueprint $table) {
                $table->id();
                $table->string('gl_number', 60)->unique()->index();
                $table->string('issuing_agency', 50)->index(); // PCSO, DSWD, DOH_MAIP, LGU, OP
                $table->foreignId('patient_account_id')->constrained('patient_accounts')->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();

                // Financial amounts using DECIMAL(15, 4) strictly
                $table->decimal('authorized_amount', 15, 4);
                $table->decimal('utilized_amount', 15, 4)->default(0);
                $table->decimal('remaining_amount', 15, 4);

                $table->string('status', 30)->default('ACTIVE')->index(); // ACTIVE, APPLIED, DEPLETED, EXPIRED
                $table->date('issued_date')->index();
                $table->date('valid_until')->nullable();
                $table->string('diagnosis')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guarantee_letters');

        Schema::table('patient_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'patient_type',
                'is_nbb',
                'philhealth_member_type',
                'philhealth_id_number',
            ]);
        });
    }
};
