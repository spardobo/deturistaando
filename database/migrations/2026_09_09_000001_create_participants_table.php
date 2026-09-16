<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('experience_id')->index();
            $table->string('name');
            $table->timestampsTz();
            $table->string('created_by_type');
            $table->uuid('created_by_public_id')->nullable();
            $table->string('updated_by_type')->nullable();
            $table->uuid('updated_by_public_id')->nullable();
            $table->softDeletesTz();
            $table->string('deleted_by_type')->nullable();
            $table->uuid('deleted_by_public_id')->nullable();
            $table->foreign('experience_id')->references('id')->on('experiences')->restrictOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
