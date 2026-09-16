<?php

use App\Models\Experience;
use App\Models\Participant;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->now = CarbonImmutable::parse('2026-09-09 12:00:00 UTC');
    CarbonImmutable::setTestNow($this->now);
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

test('guest sees only eligible public experiences on the Livewire discovery page', function (): void {
    Experience::factory()->active()->create(['title' => 'Market morning']);
    Experience::factory()->upcoming()->create(['title' => 'Tomorrow market']);
    Experience::factory()->draft()->active()->create(['title' => 'Draft market']);
    Experience::factory()->finished()->create(['title' => 'Finished market']);

    $this->get('/discover')
        ->assertOk()
        ->assertSeeText('Discover experiences')
        ->assertSeeText('Market morning')
        ->assertSeeText('Tomorrow market')
        ->assertDontSeeText('Draft market')
        ->assertDontSeeText('Finished market');
});

test('guest retains an authorized exact locality filter in the discovery URL', function (): void {
    Experience::factory()->active()->create(['title' => 'Madrid market', 'locality' => 'Madrid']);
    Experience::factory()->active()->create(['title' => 'Seville market', 'locality' => 'Seville']);

    $this->get('/discover?location=Madrid')
        ->assertOk()
        ->assertSeeText('Madrid market')
        ->assertDontSeeText('Seville market')
        ->assertSee('&quot;location&quot;:&quot;Madrid&quot;', false)
        ->assertSee('&quot;use&quot;:&quot;push&quot;', false);
});

test('discovery cards expose only approved public experience fields', function (): void {
    $experience = Experience::factory()->active()->create([
        'title' => 'Market morning',
        'public_id' => '0198da00-0000-7000-8000-000000000001',
    ]);
    Participant::factory()->for($experience)->create(['name' => 'Private participant']);

    $this->get('/discover')
        ->assertOk()
        ->assertSeeText('Market morning')
        ->assertDontSeeText($experience->public_id)
        ->assertDontSeeText('Private participant');
});

test('guest receives a field error and no cards for an array-valued locality', function (): void {
    Experience::factory()->active()->create(['title' => 'Market morning', 'locality' => 'Madrid']);

    $this->get('/discover?location%5B%5D=Madrid')
        ->assertOk()
        ->assertSeeText('Choose a valid locality.')
        ->assertDontSeeText('Market morning')
        ->assertSee('href="/discover"', false);
});

test('guest receives a field error and no cards for a malformed date', function (): void {
    Experience::factory()->active()->create(['title' => 'Market morning']);

    $this->get('/discover?date=not-a-date')
        ->assertOk()
        ->assertSeeText('Choose a valid date.')
        ->assertDontSeeText('Market morning');

    $this->get('/discover?date=2026-02-30')
        ->assertOk()
        ->assertSeeText('Choose a valid date.')
        ->assertDontSeeText('Market morning');
});

test('Livewire search restores a malformed date error after resetting errors', function (): void {
    Experience::factory()->active()->create(['title' => 'Market morning']);

    $component = Livewire::test('pages::discovery.index')
        ->set('date', 'not-a-date')
        ->call('search');

    $component
        ->assertHasErrors(['date'])
        ->assertSeeText('Choose a valid date.')
        ->assertDontSeeText('Market morning');

    $component
        ->set('date', $this->now->format('Y-m-d'))
        ->call('search')
        ->assertHasNoErrors()
        ->assertSeeText('Market morning');
});
