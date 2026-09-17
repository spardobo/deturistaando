<?php

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('status rename preserves rows and discovery invariants through rollback and reapplication', function (): void {
    $this->assertSame('pgsql', DB::connection()->getDriverName());
    $this->assertSame('testing', DB::connection()->getDatabaseName());
    $this->assertTrue(Schema::hasColumn('experiences', 'status'));
    $this->assertFalse(Schema::hasColumn('experiences', 'editorial_status'));

    $migration = require database_path('migrations/2026_09_17_000000_rename_experiences_editorial_status_to_status.php');
    $migration->down();

    foreach (['draft', 'published', 'cancelled', 'published'] as $offset => $status) {
        DB::table('experiences')->insert([
            'public_id' => (string) str()->uuid7(),
            'title' => 'Existing '.$status,
            'locality' => 'Madrid',
            'category' => 'Food',
            'audience' => 'Everyone',
            'editorial_status' => $status,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'timezone' => 'Europe/Madrid',
            'created_by_type' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $offset === 3 ? now() : null,
        ]);
    }

    $rows = DB::table('experiences')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    $indexOid = DB::scalar("SELECT 'experiences_discovery_schedule_index'::regclass::oid");

    foreach (['status', 'editorial_status', 'status'] as $column) {
        if ($column === 'status') {
            $migration->up();
        } else {
            $migration->down();
        }
        $otherColumn = $column === 'status' ? 'editorial_status' : 'status';
        $constraint = 'experiences_'.$column.'_check';

        $this->assertTrue(Schema::hasColumn('experiences', $column));
        $this->assertFalse(Schema::hasColumn('experiences', $otherColumn));
        $actual = DB::table('experiences')->orderBy('id')->get()->map(function ($row) use ($column) {
            $attributes = (array) $row;
            $attributes['editorial_status'] = $attributes[$column];
            if ($column === 'status') {
                unset($attributes['status']);
            }

            return $attributes;
        })->all();
        $this->assertEquals($rows, $actual);
        $this->assertSame($indexOid, DB::scalar("SELECT 'experiences_discovery_schedule_index'::regclass::oid"));

        $predicate = DB::scalar("SELECT pg_get_expr(indpred, indrelid) FROM pg_index WHERE indexrelid = 'experiences_discovery_schedule_index'::regclass");
        $this->assertStringContainsString($column, $predicate);
        $this->assertSame(
            DB::table('experiences')->where($column, 'published')->whereNull('deleted_at')->pluck('id')->all(),
            DB::table('experiences')->whereRaw($predicate)->pluck('id')->all(),
        );

        foreach (['unknown', 'upcoming', 'active', 'finished'] as $invalid) {
            try {
                DB::transaction(fn () => DB::table('experiences')->where('id', $rows[0]['id'])->update([$column => $invalid]));
                $this->fail('The status constraint accepted '.$invalid);
            } catch (QueryException $exception) {
                $this->assertSame('23514', $exception->errorInfo[0]);
                $this->assertStringContainsString($constraint, $exception->getMessage());
            }
        }
    }
});
