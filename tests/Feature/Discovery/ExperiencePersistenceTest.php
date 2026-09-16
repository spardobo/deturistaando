<?php

namespace Tests\Feature\Discovery;

use App\Enums\ExperienceEditorialStatus;
use App\Models\Experience;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExperiencePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_experiences_have_the_required_persistence_baseline(): void
    {
        $this->assertTrue(Schema::hasColumns('experiences', [
            'id', 'public_id', 'title', 'locality', 'category', 'audience',
            'editorial_status', 'starts_at', 'ends_at', 'timezone',
            'created_at', 'created_by_type', 'created_by_public_id',
            'updated_at', 'updated_by_type', 'updated_by_public_id',
            'deleted_at', 'deleted_by_type', 'deleted_by_public_id',
        ]));
    }

    public function test_experience_lifecycle_columns_use_postgresql_timestamptz(): void
    {
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
    }

    public function test_experience_generates_a_uuidv7_public_identity_and_uses_immutable_dates(): void
    {
        $experience = Experience::query()->create($this->attributes());
        $experience->delete();
        $experience->refresh();

        $this->assertSame('7', $experience->public_id[14]);
        $this->assertInstanceOf(CarbonImmutable::class, $experience->starts_at);
        $this->assertInstanceOf(CarbonImmutable::class, $experience->deleted_at);
    }

    public function test_created_actor_metadata_is_required_without_a_database_default(): void
    {
        $column = DB::table('information_schema.columns')
            ->where('table_schema', 'public')
            ->where('table_name', 'experiences')
            ->where('column_name', 'created_by_type')
            ->first();

        $this->assertSame('NO', $column->is_nullable);
        $this->assertNull($column->column_default);
        $this->expectException(QueryException::class);

        DB::table('experiences')->insert([
            ...$this->attributes(),
            'public_id' => (string) str()->uuid7(),
            'created_by_type' => null,
        ]);
    }

    public function test_database_rejects_an_unsupported_editorial_status(): void
    {
        $this->expectException(QueryException::class);

        DB::table('experiences')->insert([
            ...$this->attributes(),
            'public_id' => (string) str()->uuid7(),
            'editorial_status' => 'unknown',
        ]);
    }

    public function test_database_rejects_an_inverted_schedule(): void
    {
        $this->expectException(QueryException::class);

        DB::table('experiences')->insert([
            ...$this->attributes(),
            'public_id' => (string) str()->uuid7(),
            'starts_at' => now()->addDay(),
            'ends_at' => now(),
        ]);
    }

    public function test_public_id_remains_unique_after_soft_deletion(): void
    {
        $experience = Experience::query()->create($this->attributes());
        $experience->delete();

        $this->expectException(QueryException::class);

        Experience::query()->create($this->attributes(['public_id' => $experience->public_id]));
    }

    public function test_eloquent_rejects_an_invalid_iana_timezone(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Experience::query()->create($this->attributes(['timezone' => 'Europe/Narnia']));
    }

    /** @return array<string, mixed> */
    private function attributes(array $overrides = []): array
    {
        return [...[
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
    }
}
