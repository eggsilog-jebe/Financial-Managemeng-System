<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_periods', function (Blueprint $table) {
            if (! Schema::hasColumn('fiscal_periods', 'closing_journal_entry_id')) {
                $table->foreignId('closing_journal_entry_id')
                    ->nullable()
                    ->after('closed_at')
                    ->constrained('journal_entries')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_periods', function (Blueprint $table) {
            if (Schema::hasColumn('fiscal_periods', 'closing_journal_entry_id')) {
                $table->dropForeign(['closing_journal_entry_id']);
                $table->dropColumn('closing_journal_entry_id');
            }
        });
    }
};
