<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('title');
            $table->string('locality');
            $table->string('category');
            $table->string('audience');
            $table->string('editorial_status');
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->string('timezone');
            $table->timestampsTz();
            $table->string('created_by_type');
            $table->uuid('created_by_public_id')->nullable();
            $table->string('updated_by_type')->nullable();
            $table->uuid('updated_by_public_id')->nullable();
            $table->softDeletesTz();
            $table->string('deleted_by_type')->nullable();
            $table->uuid('deleted_by_public_id')->nullable();
        });

        DB::statement("ALTER TABLE experiences ADD CONSTRAINT experiences_editorial_status_check CHECK (editorial_status IN ('draft', 'published', 'cancelled'))");
        DB::statement('ALTER TABLE experiences ADD CONSTRAINT experiences_schedule_check CHECK (starts_at <= ends_at)');
        DB::statement("CREATE INDEX experiences_discovery_schedule_index ON experiences (ends_at, starts_at) WHERE deleted_at IS NULL AND editorial_status = 'published'");
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
