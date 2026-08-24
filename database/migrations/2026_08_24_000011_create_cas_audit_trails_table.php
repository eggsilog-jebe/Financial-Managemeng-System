<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cas_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->string('event_uuid', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('auditable_type', 100)->index(); // Model class
            $table->unsignedBigInteger('auditable_id')->index();
            $table->enum('action', ['INSERT', 'UPDATE', 'DELETE', 'POST', 'REVERSE', 'LOCK'])->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('record_hash', 64)->index(); // SHA256 cryptographic seal
            $table->string('previous_hash', 64)->nullable()->index(); // Blockchain-style tamper-evident chain
            $table->timestamp('created_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cas_audit_trails');
    }
};
