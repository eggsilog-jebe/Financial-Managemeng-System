<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bank_name');
            $table->string('account_number')->unique();
            $table->string('gl_code', 30)->index();
            $table->string('purpose');
            $table->string('currency', 10)->default('PHP');
            $table->decimal('balance', 15, 4)->default(0.0000);
            $table->enum('status', ['Active', 'Inactive', 'Frozen'])->default('Active')->index();
            $table->timestamps();
        });

        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->string('source_account');
            $table->string('source_number');
            $table->string('destination_account');
            $table->string('destination_number');
            $table->decimal('amount', 15, 4);
            $table->string('transfer_method');
            $table->date('transfer_date')->index();
            $table->string('status')->default('Completed & Posted')->index();
            $table->timestamps();
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('statement_date')->index();
            $table->decimal('statement_balance', 15, 4);
            $table->decimal('book_balance', 15, 4);
            $table->decimal('variance', 15, 4)->default(0.0000);
            $table->string('status')->default('Reconciled')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('bank_accounts');
    }
};
