<?php

use App\Enums\ExperienceEditorialStatus;
use App\Models\Experience;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

$attributes = fn (array $overrides = []): array => [...[
    'title' => 'Market morning',
    'locality' => 'Madrid',
    'category' => 'Food',
    'audience' => 'Everyone',
    'editorial_status' => ExperienceEditorialStatus::Published,
    'starts_at' => now()->subHour(),
    'ends_at' => now()->addHour(),
    'timezone' => 'Europe/Madrid',
    'created_by_type' => 'system',
    'created_by_public_id' => null,
    'updated_by_type' => null,
    'updated_by_public_id' => null,
    'deleted_by_type' => null,
    'deleted_by_public_id' => null,
    'created_at' => now(),
    'updated_at' => now(),
], ...$overrides];

test('experiences have the required persistence baseline', function (): void {
    $this->assertTrue(Schema::hasColumns('experiences', [
        'id', 'public_id', 'title', 'locality', 'category', 'audience',
        'editorial_status', 'starts_at', 'ends_at', 'timezone',
        'created_at', 'created_by_type', 'created_by_public_id',
        'updated_at', 'updated_by_type', 'updated_by_public_id',
        'deleted_at', 'deleted_by_type', 'deleted_by_public_id',
    ]));
});

test('experience lifecycle columns use postgresql timestamptz', function (): void {
    $types = DB::table('information_schema.columns')
        ->where('table_schema', 'public')
        ->where('table_name', 'experiences')
        ->whereIn('column_name', ['created_at', 'updated_at', 'deleted_at'])
        ->pluck('data_type', 'column_name')
        ->all();

    $this->assertSame([
        'created_at' => 'timestamp with time zone',
        'deleted_at' => 'timestamp with time zone',
        'updated_at' => 'timestamp with time zone',
    ], $types);
});

test('experience generates a uuidv7 public identity and uses immutable dates', function () use ($attributes): void {
    $experience = Experience::query()->create($attributes());
    $experience->delete();
    $experience->refresh();

    $this->assertSame('7', $experience->public_id[14]);
    $this->assertInstanceOf(CarbonImmutable::class, $experience->starts_at);
    $this->assertInstanceOf(CarbonImmutable::class, $experience->deleted_at);
});

test('created actor metadata is required without a database default', function () use ($attributes): void {
    $column = DB::table('information_schema.columns')
        ->where('table_schema', 'public')
        ->where('table_name', 'experiences')
        ->where('column_name', 'created_by_type')
        ->first();

    $this->assertSame('NO', $column->is_nullable);
    $this->assertNull($column->column_default);
    $this->expectException(QueryException::class);

    DB::table('experiences')->insert([
        ...$attributes(),
        'public_id' => (string) str()->uuid7(),
        'created_by_type' => null,
    ]);
});

test('database rejects an unsupported editorial status', function () use ($attributes): void {
    $this->expectException(QueryException::class);

    DB::table('experiences')->insert([
        ...$attributes(),
        'public_id' => (string) str()->uuid7(),
        'editorial_status' => 'unknown',
    ]);
});

test('database rejects an inverted schedule', function () use ($attributes): void {
    $this->expectException(QueryException::class);

    DB::table('experiences')->insert([
        ...$attributes(),
        'public_id' => (string) str()->uuid7(),
        'starts_at' => now()->addDay(),
        'ends_at' => now(),
    ]);
});

test('public id remains unique after soft deletion', function () use ($attributes): void {
    $experience = Experience::query()->create($attributes());
    $experience->delete();

    $this->expectException(QueryException::class);

    Experience::query()->create($attributes(['public_id' => $experience->public_id]));
});

test('eloquent rejects an invalid iana timezone', function () use ($attributes): void {
    $this->expectException(InvalidArgumentException::class);

    Experience::query()->create($attributes(['timezone' => 'Europe/Narnia']));
});
