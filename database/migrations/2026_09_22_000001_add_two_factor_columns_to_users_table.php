<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add TOTP 2FA columns to the users table.
     * All secrets are encrypted at rest via Laravel's encryption layer.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Encrypted TOTP Base32 secret key
            $table->text('two_factor_secret')->nullable()->after('password');

            // Encrypted JSON array of one-time recovery codes (hashed)
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');

            // Timestamp of when the user confirmed 2FA enrollment (null = not enrolled)
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });
    }
};
