<?php

namespace Tests\Feature\Discovery;

use App\Enums\ExperienceEditorialStatus;
use App\Models\Experience;
use App\Models\Participant;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ExperienceFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-09 12:00:00 UTC'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_factories_create_a_published_active_experience_and_related_participant(): void
    {
        $experience = Experience::factory()->create();
        $participant = Participant::factory()->create();

        $this->assertSame('Market morning', $experience->title);
        $this->assertSame('Madrid', $experience->locality);
        $this->assertSame('Food', $experience->category);
        $this->assertSame('Everyone', $experience->audience);
        $this->assertSame(ExperienceEditorialStatus::Published, $experience->editorial_status);
        $this->assertSame('Europe/Madrid', $experience->timezone);
        $this->assertSame('2026-09-09T11:00:00+00:00', $experience->starts_at->toAtomString());
        $this->assertSame('2026-09-09T13:00:00+00:00', $experience->ends_at->toAtomString());
        $this->assertSame('system', $experience->created_by_type);
        $this->assertSame(7, Uuid::fromString($experience->public_id)->getVersion());
        $this->assertSame(7, Uuid::fromString($participant->public_id)->getVersion());
        $this->assertDatabaseHas('experiences', ['id' => $participant->experience_id]);
        $this->assertTrue($participant->experience->is(Experience::findOrFail($participant->experience_id)));
        $this->assertSame('system', $participant->created_by_type);
    }

    public function test_experience_factory_editorial_and_temporal_states_are_deterministic(): void
    {
        $draft = Experience::factory()->draft()->active()->create();
        $cancelled = Experience::factory()->cancelled()->upcoming()->create();
        $finished = Experience::factory()->published()->finished()->create();

        $this->assertSame(ExperienceEditorialStatus::Draft, $draft->editorial_status);
        $this->assertSame('2026-09-09T11:00:00+00:00', $draft->starts_at->toAtomString());
        $this->assertSame('2026-09-09T13:00:00+00:00', $draft->ends_at->toAtomString());
        $this->assertSame(ExperienceEditorialStatus::Cancelled, $cancelled->editorial_status);
        $this->assertSame('2026-09-09T13:00:00+00:00', $cancelled->starts_at->toAtomString());
        $this->assertSame('2026-09-09T14:00:00+00:00', $cancelled->ends_at->toAtomString());
        $this->assertSame(ExperienceEditorialStatus::Published, $finished->editorial_status);
        $this->assertSame('2026-09-09T10:00:00+00:00', $finished->starts_at->toAtomString());
        $this->assertSame('2026-09-09T11:00:00+00:00', $finished->ends_at->toAtomString());
    }

    public function test_factories_preserve_explicit_values_and_support_soft_deleted_relationship_data(): void
    {
        $publicId = (string) str()->uuid7();
        $experience = Experience::factory()->create([
            'public_id' => $publicId,
            'title' => 'Garden evening',
            'locality' => 'Seville',
            'category' => 'Music',
            'audience' => 'Adults',
            'timezone' => 'Europe/London',
        ]);
        $deleted = Experience::factory()->trashed()->create();
        $participantPublicId = '0198f5d3-76b4-7000-8000-000000000001';
        $participant = Participant::factory()->for($experience)->create([
            'name' => 'Garden stand',
            'public_id' => $participantPublicId,
        ]);
        $deletedParticipant = Participant::factory()->for($experience)->trashed()->create();

        $this->assertSame($publicId, $experience->public_id);
        $this->assertSame(['Garden evening', 'Seville', 'Music', 'Adults', 'Europe/London'], [
            $experience->title,
            $experience->locality,
            $experience->category,
            $experience->audience,
            $experience->timezone,
        ]);
        $this->assertSame('system', $experience->created_by_type);
        $this->assertNotNull($deleted->deleted_at);
        $this->assertNull(Experience::query()->find($deleted->id));
        $this->assertSame($deleted->id, Experience::withTrashed()->findOrFail($deleted->id)->id);
        $this->assertSame('Garden stand', $participant->name);
        $this->assertSame($participantPublicId, $participant->public_id);
        $this->assertTrue($participant->experience->is($experience));
        $this->assertNotNull($deletedParticipant->deleted_at);
        $this->assertNull(Participant::query()->find($deletedParticipant->id));
        $this->assertTrue(Participant::withTrashed()->findOrFail($deletedParticipant->id)->experience->is($experience));
    }
}
