<?php

namespace Tests\Feature\Discovery;

use App\Models\Experience;
use App\Models\Participant;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DiscoverExperiencesTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = CarbonImmutable::parse('2026-09-09 12:00:00 UTC');
        CarbonImmutable::setTestNow($this->now);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_guest_sees_only_eligible_public_experiences_on_the_livewire_discovery_page(): void
    {
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
    }

    public function test_guest_retains_an_authorized_exact_locality_filter_in_the_discovery_url(): void
    {
        Experience::factory()->active()->create(['title' => 'Madrid market', 'locality' => 'Madrid']);
        Experience::factory()->active()->create(['title' => 'Seville market', 'locality' => 'Seville']);

        $this->get('/discover?location=Madrid')
            ->assertOk()
            ->assertSeeText('Madrid market')
            ->assertDontSeeText('Seville market')
            ->assertSee('&quot;location&quot;:&quot;Madrid&quot;', false)
            ->assertSee('&quot;use&quot;:&quot;push&quot;', false);
    }

    public function test_discovery_cards_expose_only_approved_public_experience_fields(): void
    {
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
    }

    public function test_guest_receives_a_field_error_and_no_cards_for_an_array_valued_locality(): void
    {
        Experience::factory()->active()->create(['title' => 'Market morning', 'locality' => 'Madrid']);

        $this->get('/discover?location%5B%5D=Madrid')
            ->assertOk()
            ->assertSeeText('Choose a valid locality.')
            ->assertDontSeeText('Market morning')
            ->assertSee('href="/discover"', false);
    }

    public function test_guest_receives_a_field_error_and_no_cards_for_a_malformed_date(): void
    {
        Experience::factory()->active()->create(['title' => 'Market morning']);

        $this->get('/discover?date=not-a-date')
            ->assertOk()
            ->assertSeeText('Choose a valid date.')
            ->assertDontSeeText('Market morning');

        $this->get('/discover?date=2026-02-30')
            ->assertOk()
            ->assertSeeText('Choose a valid date.')
            ->assertDontSeeText('Market morning');
    }

    public function test_livewire_search_restores_a_malformed_date_error_after_resetting_errors(): void
    {
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
    }
}
