<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['experiences', 'participants'] as $table) {
            foreach (['created', 'updated', 'deleted'] as $event) {
                DB::statement("ALTER TABLE public.{$table} RENAME COLUMN {$event}_by_public_id TO {$event}_by_id");
                DB::statement(<<<SQL
                    ALTER TABLE public.{$table}
                    ALTER COLUMN {$event}_by_id TYPE varchar(255) USING {$event}_by_id::text
                    SQL);
            }
        }
    }

    /**
     * Rejects rollback when native identifiers cannot be represented as UUIDs.
     * Keep the migrated schema and roll forward if decimal user IDs exist.
     * If reversal is mandatory, stop writers and restore a verified pre-migration
     * backup through an operator-approved recovery plan; never erase or remap actors.
     *
     * @throws RuntimeException When any actor identifier is incompatible with UUID storage.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            DB::statement('LOCK TABLE public.experiences, public.participants IN ACCESS EXCLUSIVE MODE');

            foreach (['experiences', 'participants'] as $table) {
                foreach (['created', 'updated', 'deleted'] as $event) {
                    $incompatible = DB::table('public.'.$table)
                        ->whereNotNull($event.'_by_id')
                        ->whereRaw("NOT pg_input_is_valid({$event}_by_id, 'uuid')")
                        ->exists();

                    if ($incompatible) {
                        throw new RuntimeException(
                            'Cannot roll back native actor identifiers: incompatible UUID storage. '
                            .'Keep this schema and roll forward, or stop writers and restore a verified '
                            .'pre-migration backup under an operator-approved recovery plan. Never erase or remap actors.'
                        );
                    }
                }
            }

            foreach (['experiences', 'participants'] as $table) {
                foreach (['created', 'updated', 'deleted'] as $event) {
                    DB::statement(<<<SQL
                        ALTER TABLE public.{$table}
                        ALTER COLUMN {$event}_by_id TYPE uuid USING {$event}_by_id::uuid
                        SQL);
                    DB::statement("ALTER TABLE public.{$table} RENAME COLUMN {$event}_by_id TO {$event}_by_public_id");
                }
            }
        });
    }
};
