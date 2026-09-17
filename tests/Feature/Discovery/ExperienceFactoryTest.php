<?php

use App\Enums\ExperienceStatus;
use App\Models\Experience;
use App\Models\Participant;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-09 12:00:00 UTC'));
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('factories create a published active experience and related participant', function (): void {
    $experience = Experience::factory()->create();
    $participant = Participant::factory()->create();

    $this->assertSame('Market morning', $experience->title);
    $this->assertSame('Madrid', $experience->locality);
    $this->assertSame('Food', $experience->category);
    $this->assertSame('Everyone', $experience->audience);
    $this->assertSame(ExperienceStatus::Published, $experience->status);
    $this->assertSame('Europe/Madrid', $experience->timezone);
    $this->assertSame('2026-09-09T11:00:00+00:00', $experience->starts_at->toAtomString());
    $this->assertSame('2026-09-09T13:00:00+00:00', $experience->ends_at->toAtomString());
    $this->assertSame('system', $experience->created_by_type);
    $this->assertSame(7, Uuid::fromString($experience->public_id)->getVersion());
    $this->assertSame(7, Uuid::fromString($participant->public_id)->getVersion());
    $this->assertDatabaseHas('experiences', ['id' => $participant->experience_id]);
    $this->assertTrue($participant->experience->is(Experience::findOrFail($participant->experience_id)));
    $this->assertSame('system', $participant->created_by_type);
});

test('experience factory editorial and temporal states are deterministic', function (): void {
    $draft = Experience::factory()->draft()->active()->create();
    $cancelled = Experience::factory()->cancelled()->upcoming()->create();
    $finished = Experience::factory()->published()->finished()->create();

    $this->assertSame(ExperienceStatus::Draft, $draft->status);
    $this->assertSame('2026-09-09T11:00:00+00:00', $draft->starts_at->toAtomString());
    $this->assertSame('2026-09-09T13:00:00+00:00', $draft->ends_at->toAtomString());
    $this->assertSame(ExperienceStatus::Cancelled, $cancelled->status);
    $this->assertSame('2026-09-09T13:00:00+00:00', $cancelled->starts_at->toAtomString());
    $this->assertSame('2026-09-09T14:00:00+00:00', $cancelled->ends_at->toAtomString());
    $this->assertSame(ExperienceStatus::Published, $finished->status);
    $this->assertSame('2026-09-09T10:00:00+00:00', $finished->starts_at->toAtomString());
    $this->assertSame('2026-09-09T11:00:00+00:00', $finished->ends_at->toAtomString());
});

test('factories preserve explicit values and support soft deleted relationship data', function (): void {
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
});
