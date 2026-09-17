<?php

use App\Models\Experience;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->now = CarbonImmutable::parse('2026-09-09 12:00:00 UTC');
    CarbonImmutable::setTestNow($this->now);
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('returns published unfinished candidates in deterministic discovery order', function (): void {
    $later = Experience::factory()->upcoming()->create(['title' => 'Later', 'starts_at' => $this->now->addHours(2)]);
    $sameStartZulu = Experience::factory()->upcoming()->create(['title' => 'Zulu', 'starts_at' => $this->now->addHour()]);
    $sameStartAlpha = Experience::factory()->upcoming()->create(['title' => 'Alpha', 'starts_at' => $this->now->addHour()]);
    $active = Experience::factory()->active()->create(['title' => 'Active']);
    Experience::factory()->draft()->active()->create();
    Experience::factory()->cancelled()->upcoming()->create();
    Experience::factory()->finished()->create();
    Experience::factory()->trashed()->active()->create();

    $results = Experience::query()->discoverableAt($this->now)->orderedForDiscovery()->get();

    $this->assertEquals([$active->id, $sameStartAlpha->id, $sameStartZulu->id, $later->id], $results->pluck('id')->all());
});

it('classifies active and upcoming scopes at inclusive schedule boundaries', function (): void {
    $atStart = Experience::factory()->create(['starts_at' => $this->now, 'ends_at' => $this->now->addHour()]);
    $atEnd = Experience::factory()->create(['starts_at' => $this->now->subHour(), 'ends_at' => $this->now]);
    $upcoming = Experience::factory()->upcoming()->create();

    $candidate = Experience::query()->discoverableAt($this->now);

    $this->assertEqualsCanonicalizing([$atStart->id, $atEnd->id], $candidate->clone()->activeAt($this->now)->pluck('id')->all());
    $this->assertSame([$upcoming->id], $candidate->clone()->upcomingAt($this->now)->pluck('id')->all());
});

it('rejects mismatched exact facets and composes them with local date and state', function (): void {
    $activeMatch = Experience::factory()->active()->create([
        'locality' => 'Seville',
        'category' => 'Music',
        'audience' => 'Adults',
    ]);
    $upcomingMatch = Experience::factory()->upcoming()->create([
        'locality' => 'Seville',
        'category' => 'Music',
        'audience' => 'Adults',
    ]);
    $wrongLocality = Experience::factory()->active()->create([
        'locality' => 'Cadiz',
        'category' => 'Music',
        'audience' => 'Adults',
    ]);
    $wrongCategory = Experience::factory()->active()->create([
        'locality' => 'Seville',
        'category' => 'Food',
        'audience' => 'Adults',
    ]);
    $wrongAudience = Experience::factory()->active()->create([
        'locality' => 'Seville',
        'category' => 'Music',
        'audience' => 'Families',
    ]);

    $facetedCandidates = Experience::query()
        ->discoverableAt($this->now)
        ->onLocalDate('2026-09-09')
        ->inLocality('Seville')
        ->inCategory('Music')
        ->forAudience('Adults');

    $activeResults = $facetedCandidates->clone()->activeAt($this->now)->pluck('id')->all();

    $this->assertSame([$activeMatch->id], $activeResults);
    $this->assertNotContains($wrongLocality->id, $activeResults);
    $this->assertNotContains($wrongCategory->id, $activeResults);
    $this->assertNotContains($wrongAudience->id, $activeResults);
    $this->assertSame([$upcomingMatch->id], $facetedCandidates->clone()->upcomingAt($this->now)->pluck('id')->all());
    $this->assertSame([], Experience::query()
        ->discoverableAt($this->now)
        ->onLocalDate('2026-09-09')
        ->inLocality('Seville')
        ->inCategory('Food')
        ->forAudience('Families')
        ->upcomingAt($this->now)
        ->pluck('id')
        ->all());
});

it('uses record local dates and rejects adjacent and daylight saving control rows', function (): void {
    $utcCrossing = Experience::factory()->active()->create([
        'timezone' => 'America/New_York',
        'starts_at' => CarbonImmutable::parse('2026-09-10 03:30:00 UTC'),
        'ends_at' => CarbonImmutable::parse('2026-09-10 03:45:00 UTC'),
    ]);
    $dstCrossing = Experience::factory()->upcoming()->create([
        'timezone' => 'Europe/Madrid',
        'starts_at' => CarbonImmutable::parse('2026-10-24 22:30:00 UTC'),
        'ends_at' => CarbonImmutable::parse('2026-10-25 02:30:00 UTC'),
    ]);
    $dstControl = Experience::factory()->upcoming()->create([
        'timezone' => 'Europe/Madrid',
        'starts_at' => CarbonImmutable::parse('2026-10-24 20:30:00 UTC'),
        'ends_at' => CarbonImmutable::parse('2026-10-24 21:30:00 UTC'),
    ]);

    $this->assertSame([$utcCrossing->id], Experience::query()->onLocalDate('2026-09-09')->pluck('id')->all());
    $this->assertSame([], Experience::query()->onLocalDate('2026-09-08')->pluck('id')->all());
    $this->assertSame([], Experience::query()->onLocalDate('2026-09-10')->pluck('id')->all());
    $this->assertSame([$dstCrossing->id], Experience::query()->onLocalDate('2026-10-25')->pluck('id')->all());
    $this->assertSame([$dstControl->id], Experience::query()->onLocalDate('2026-10-24')->pluck('id')->all());
});

it('uses the internal ID as the final discovery ordering tie breaker', function (): void {
    $first = Experience::factory()->upcoming()->create([
        'title' => 'Same title',
        'starts_at' => $this->now->addHour(),
    ]);
    $second = Experience::factory()->upcoming()->create([
        'title' => 'Same title',
        'starts_at' => $this->now->addHour(),
    ]);

    $ids = Experience::query()->discoverableAt($this->now)->orderedForDiscovery()->pluck('id')->all();

    $this->assertSame([$first->id, $second->id], $ids);
});
