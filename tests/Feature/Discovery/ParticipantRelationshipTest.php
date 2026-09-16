<?php

namespace Tests\Feature\Discovery;

use App\Models\Experience;
use App\Models\Participant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ParticipantRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_belongs_to_an_experience_and_has_required_baseline_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('participants', [
            'id', 'public_id', 'experience_id', 'name', 'created_at', 'updated_at', 'deleted_at',
            'created_by_type', 'created_by_public_id', 'updated_by_type', 'updated_by_public_id',
            'deleted_by_type', 'deleted_by_public_id',
        ]));

        $experience = Experience::query()->create($this->experienceAttributes());
        $participant = Participant::query()->create($this->participantAttributes($experience->id));

        $this->assertTrue($experience->participants()->whereKey($participant)->exists());
        $this->assertTrue($participant->experience()->is($experience));
        $this->assertSame('7', $participant->public_id[14]);
    }

    public function test_participant_lifecycle_columns_use_postgresql_timestamptz_without_a_creator_default(): void
    {
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
    }

    public function test_participant_public_id_remains_unique_after_soft_deletion(): void
    {
        $experience = Experience::query()->create($this->experienceAttributes());
        $participant = Participant::query()->create($this->participantAttributes($experience->id));
        $participant->delete();

        $this->expectException(QueryException::class);

        Participant::query()->create($this->participantAttributes($experience->id, ['public_id' => $participant->public_id]));
    }

    public function test_participant_soft_deletion_hides_the_record_from_ordinary_queries(): void
    {
        $experience = Experience::query()->create($this->experienceAttributes());
        $participant = Participant::query()->create($this->participantAttributes($experience->id));
        $participant->delete();

        $this->assertNull(Participant::query()->find($participant->id));
        $this->assertSame($participant->id, Participant::withTrashed()->findOrFail($participant->id)->id);
    }

    public function test_participant_foreign_key_is_indexed_and_restricts_parent_deletion(): void
    {
        $experience = Experience::query()->create($this->experienceAttributes());
        Participant::query()->create($this->participantAttributes($experience->id));

        $index = DB::table('pg_indexes')
            ->where('schemaname', 'public')
            ->where('tablename', 'participants')
            ->where('indexdef', 'like', '%(experience_id)%')
            ->exists();

        $this->assertTrue($index);
        $this->expectException(QueryException::class);

        DB::table('experiences')->where('id', $experience->id)->delete();
    }

    public function test_participant_foreign_key_restricts_parent_key_updates(): void
    {
        $experience = Experience::query()->create($this->experienceAttributes());
        Participant::query()->create($this->participantAttributes($experience->id));

        $this->expectException(QueryException::class);

        DB::table('experiences')->where('id', $experience->id)->update(['id' => $experience->id + 100]);
    }

    /** @return array<string, mixed> */
    private function experienceAttributes(): array
    {
        return [
            'title' => 'Market morning',
            'locality' => 'Madrid',
            'category' => 'Food',
            'audience' => 'Everyone',
            'editorial_status' => 'published',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'timezone' => 'Europe/Madrid',
            'created_by_type' => 'system',
        ];
    }

    /** @return array<string, mixed> */
    private function participantAttributes(int $experienceId, array $overrides = []): array
    {
        return [...[
            'experience_id' => $experienceId,
            'name' => 'Market stand',
            'created_by_type' => 'system',
        ], ...$overrides];
    }
}
