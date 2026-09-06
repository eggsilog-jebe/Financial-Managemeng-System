<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->string('department_code', 30)->nullable()->after('department')->index();
            $table->string('category', 100)->nullable()->after('department_code')->index();
            $table->string('department_head', 100)->nullable()->after('category');
            $table->text('notes')->nullable()->after('status');
        });

        Schema::create('budget_reallocations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('source_budget_allocation_id')->constrained('budget_allocations')->restrictOnDelete();
            $table->foreignId('destination_budget_allocation_id')->constrained('budget_allocations')->restrictOnDelete();
            $table->string('source_department');
            $table->string('destination_department');
            $table->string('fiscal_year', 10)->index();
            $table->decimal('amount', 15, 4);
            $table->date('transfer_date')->index();
            $table->text('reason');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('APPROVED')->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_reallocations');

        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->dropColumn(['department_code', 'category', 'department_head', 'notes']);
        });
    }
};
