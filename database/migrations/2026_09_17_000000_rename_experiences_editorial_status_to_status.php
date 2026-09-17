<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renames publication status while preserving rows and dependent expressions.
     */
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table): void {
            $table->renameColumn('editorial_status', 'status');
        });

        DB::statement('ALTER TABLE experiences RENAME CONSTRAINT experiences_editorial_status_check TO experiences_status_check');
    }

    /**
     * Restores the previous names without changing publication values.
     */
    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table): void {
            $table->renameColumn('status', 'editorial_status');
        });

        DB::statement('ALTER TABLE experiences RENAME CONSTRAINT experiences_status_check TO experiences_editorial_status_check');
    }
};
