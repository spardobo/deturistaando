<?php

use App\Models\Experience;
use App\Models\Participant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

$experienceAttributes = fn (): array => [
    'title' => 'Market morning',
    'locality' => 'Madrid',
    'category' => 'Food',
    'audience' => 'Everyone',
    'status' => 'published',
    'starts_at' => now()->subHour(),
    'ends_at' => now()->addHour(),
    'timezone' => 'Europe/Madrid',
    'created_by_type' => 'system',
];

$participantAttributes = fn (int $experienceId, array $overrides = []): array => [...[
    'experience_id' => $experienceId,
    'name' => 'Market stand',
    'created_by_type' => 'system',
], ...$overrides];

test('participant belongs to an experience and has required baseline columns', function () use ($experienceAttributes, $participantAttributes): void {
    $this->assertTrue(Schema::hasColumns('participants', [
        'id', 'public_id', 'experience_id', 'name', 'created_at', 'updated_at', 'deleted_at',
        'created_by_type', 'created_by_id', 'updated_by_type', 'updated_by_id',
        'deleted_by_type', 'deleted_by_id',
    ]));

    $experience = Experience::query()->create($experienceAttributes());
    $participant = Participant::query()->create($participantAttributes($experience->id));

    $this->assertTrue($experience->participants()->whereKey($participant)->exists());
    $this->assertTrue($participant->experience()->is($experience));
    $this->assertSame('7', $participant->public_id[14]);
});

test('participant lifecycle columns use postgresql timestamptz without a creator default', function (): void {
    $types = DB::table('information_schema.columns')
        ->where('table_schema', 'public')
        ->where('table_name', 'participants')
        ->whereIn('column_name', ['created_at', 'updated_at', 'deleted_at'])
        ->pluck('data_type', 'column_name')
        ->all();
    $createdByType = DB::table('information_schema.columns')
        ->where('table_schema', 'public')->where('table_name', 'participants')
        ->where('column_name', 'created_by_type')->first();

    $this->assertSame(array_fill_keys(['created_at', 'deleted_at', 'updated_at'], 'timestamp with time zone'), $types);
    $this->assertSame('NO', $createdByType->is_nullable);
    $this->assertNull($createdByType->column_default);
});

test('participant public id remains unique after soft deletion', function () use ($experienceAttributes, $participantAttributes): void {
    $experience = Experience::query()->create($experienceAttributes());
    $participant = Participant::query()->create($participantAttributes($experience->id));
    $participant->delete();

    $this->expectException(QueryException::class);

    Participant::query()->create($participantAttributes($experience->id, ['public_id' => $participant->public_id]));
});

test('participant soft deletion hides the record from ordinary queries', function () use ($experienceAttributes, $participantAttributes): void {
    $experience = Experience::query()->create($experienceAttributes());
    $participant = Participant::query()->create($participantAttributes($experience->id));
    $participant->delete();

    $this->assertNull(Participant::query()->find($participant->id));
    $this->assertSame($participant->id, Participant::withTrashed()->findOrFail($participant->id)->id);
});

test('participant foreign key is indexed and restricts parent deletion', function () use ($experienceAttributes, $participantAttributes): void {
    $experience = Experience::query()->create($experienceAttributes());
    Participant::query()->create($participantAttributes($experience->id));

    $index = DB::table('pg_indexes')
        ->where('schemaname', 'public')
        ->where('tablename', 'participants')
        ->where('indexdef', 'like', '%(experience_id)%')
        ->exists();

    $this->assertTrue($index);
    $this->expectException(QueryException::class);

    DB::table('experiences')->where('id', $experience->id)->delete();
});

test('participant foreign key restricts parent key updates', function () use ($experienceAttributes, $participantAttributes): void {
    $experience = Experience::query()->create($experienceAttributes());
    Participant::query()->create($participantAttributes($experience->id));

    $this->expectException(QueryException::class);

    DB::table('experiences')->where('id', $experience->id)->update(['id' => $experience->id + 100]);
});
