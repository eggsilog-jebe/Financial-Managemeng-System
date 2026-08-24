<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->enum('category', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE'])->index();
            $table->enum('normal_balance', ['DEBIT', 'CREDIT']);
            $table->string('department')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->date('entry_date')->index();
            $table->string('description');
            $table->enum('type', ['GENERAL', 'ADJUSTING', 'CLOSING'])->default('GENERAL')->index();
            $table->enum('status', ['DRAFT', 'POSTED', 'REVERSED'])->default('DRAFT')->index();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('debit', 15, 4)->default(0.0000);
            $table->decimal('credit', 15, 4)->default(0.0000);
            $table->string('memo')->nullable();
            $table->timestamps();

            $table->index(['journal_entry_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
