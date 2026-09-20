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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 150)->nullable();
            $table->string('user_role', 50)->nullable()->index();
            $table->string('user_email', 150)->nullable();

            // Event & Categorization
            $table->string('event', 50)->index(); // login, logout, failed_login, created, updated, deleted, posted, reversed
            $table->string('module', 60)->index(); // Authentication, General Ledger, Billing, etc.
            $table->string('auditable_type')->nullable()->index();
            $table->unsignedBigInteger('auditable_id')->nullable()->index();

            // Context & State Diff
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Request Telemetry
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();

            // Immutable timestamp: logs are append-only (no updated_at column)
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
