<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates:
     * 1. user_workstations: Workstation/Computer binding records (1-3 per user).
     * 2. user_active_sessions: Live session registry for single active session enforcement & real-time displacement.
     */
    public function up(): void
    {
        Schema::create('user_workstations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('device_uuid', 64)->index();
            $table->string('workstation_name', 100);
            $table->string('platform', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('status', 20)->default('pending')->index(); // pending, approved, rejected, revoked
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'device_uuid'], 'uniq_user_workstations_device');
            $table->foreign('user_id', 'fk_workstations_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by', 'fk_workstations_approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('revoked_by', 'fk_workstations_revoked_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('user_active_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('session_id', 128)->unique()->index();
            $table->unsignedBigInteger('workstation_id')->nullable()->index();
            $table->string('device_uuid', 64)->nullable()->index();
            $table->string('device_name', 100)->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at')->index();
            $table->timestamp('last_activity_at')->index();
            $table->boolean('is_terminated')->default(false)->index();
            $table->string('termination_reason', 50)->nullable(); // displaced_by_new_login, revoked_by_admin, manual_logout, workstation_revoked
            $table->timestamp('terminated_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'fk_active_sessions_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('workstation_id', 'fk_active_sessions_workstation_id')->references('id')->on('user_workstations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_active_sessions');
        Schema::dropIfExists('user_workstations');
    }
};
