<?php

use App\Models\Experience;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->assertSame('pgsql', DB::connection()->getDriverName());
    $this->assertSame('testing', DB::connection()->getDatabaseName());
    $this->assertSame(16, intdiv((int) DB::scalar('SHOW server_version_num'), 10000));
    $this->assertTrue(Schema::hasColumn('experiences', 'created_by_id'));
    $this->migration = require database_path('migrations/2026_09_17_000001_use_native_actor_identifiers.php');
});

$rows = fn (string $table) => DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
$columns = fn (string $table) => DB::table('information_schema.columns')
    ->where('table_schema', 'public')->where('table_name', $table)
    ->orderBy('ordinal_position')->get()->all();

test('actor migration preserves complete rows through UUID rollback and reapplication', function () use ($rows): void {
    $this->migration->down();
    $expected = [];

    foreach ([false, true] as $deleted) {
        $experience = Experience::factory()->make()->getAttributes();
        foreach (['created', 'updated', 'deleted'] as $event) {
            unset($experience[$event.'_by_id']);
        }
        $experience['public_id'] = (string) str()->uuid7();
        $experience['created_at'] = now();
        $experience['updated_at'] = now();
        $experience['deleted_at'] = $deleted ? now() : null;
        $experienceId = DB::table('experiences')->insertGetId($experience);
        DB::table('participants')->insert([
            'experience_id' => $experienceId,
            'public_id' => (string) str()->uuid7(),
            'name' => 'Retained participant',
            'created_by_type' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => $deleted ? now() : null,
        ]);
    }

    foreach (['experiences', 'participants'] as $table) {
        $attribution = [];
        foreach (['created', 'updated', 'deleted'] as $event) {
            $attribution[$event.'_by_type'] = 'user';
            $attribution[$event.'_by_public_id'] = (string) str()->uuid7();
        }
        DB::table($table)->whereNotNull('deleted_at')->update($attribution);
        $expected[$table] = $rows($table);
    }

    foreach (['up', 'down', 'up'] as $direction) {
        $this->migration->{$direction}();
        foreach (['experiences', 'participants'] as $table) {
            $actual = $rows($table);
            foreach (['created', 'updated', 'deleted'] as $event) {
                $column = $event.($direction === 'up' ? '_by_id' : '_by_public_id');
                $absent = $event.($direction === 'up' ? '_by_public_id' : '_by_id');
                $this->assertFalse(Schema::hasColumn($table, $absent));
                $metadata = DB::table('information_schema.columns')
                    ->where('table_schema', 'public')->where('table_name', $table)
                    ->where('column_name', $column)->first();
                $this->assertSame($direction === 'up' ? 'character varying' : 'uuid', $metadata->data_type);
                $this->assertSame($direction === 'up' ? 255 : null, $metadata->character_maximum_length);
                $this->assertSame('YES', $metadata->is_nullable);
                if ($direction === 'up') {
                    foreach ($actual as &$row) {
                        $row[$event.'_by_public_id'] = $row[$column];
                        unset($row[$column]);
                    }
                    unset($row);
                }
            }
            $this->assertEquals($expected[$table], $actual);
        }
    }
});

test('native user identifiers and explicit system nulls persist through models', function (): void {
    $user = User::factory()->create();
    foreach ([Experience::class, Participant::class] as $model) {
        $record = $model::factory()->create();
        $this->assertSame('system', $record->created_by_type);
        $this->assertNull($record->created_by_id);
        foreach (['created', 'updated', 'deleted'] as $event) {
            $record->fill([$event.'_by_type' => 'user', $event.'_by_id' => (string) $user->id])->save();
            $record->refresh();
            $this->assertSame('user', $record->{$event.'_by_type'});
            $this->assertSame((string) $user->id, $record->{$event.'_by_id'});
        }
    }
});

test('rollback rejects incompatible actors before any schema or row changes', function (string $table, string $column, string $value) use ($rows, $columns): void {
    Participant::factory()->create();
    DB::table($table)->update([$column => $value]);
    $beforeRows = $beforeColumns = [];
    foreach (['experiences', 'participants'] as $name) {
        $beforeRows[$name] = $rows($name);
        $beforeColumns[$name] = $columns($name);
    }
    $ddl = [];
    DB::listen(function ($query) use (&$ddl): void {
        if (preg_match('/^\s*ALTER\s/i', $query->sql)) {
            $ddl[] = $query->sql;
        }
    });

    try {
        $this->migration->down();
        $this->fail('Rollback accepted an incompatible actor identifier.');
    } catch (RuntimeException $exception) {
        $this->assertStringContainsString('Cannot roll back native actor identifiers', $exception->getMessage());
        if ($value !== '') {
            $this->assertStringNotContainsString($value, $exception->getMessage());
        }
    }

    $this->assertSame([], $ddl);
    foreach (['experiences', 'participants'] as $name) {
        $this->assertEquals($beforeRows[$name], $rows($name));
        $this->assertEquals($beforeColumns[$name], $columns($name));
    }
})->with([
    ['experiences', 'created_by_id', '9223372036854775807'],
    ['experiences', 'updated_by_id', 'not-a-uuid'],
    ['experiences', 'deleted_by_id', ''],
    ['participants', 'created_by_id', '12345'],
    ['participants', 'updated_by_id', '00000000-0000-0000-0000-00000000000z'],
    ['participants', 'deleted_by_id', "00000000-0000-0000-0000-000000000001\n"],
]);
