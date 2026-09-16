<?php

use App\Models\Experience;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::public'), Title('Discover experiences')] class extends Component {
    #[Url(history: true, except: '')]
    public $location = '';

    #[Url(history: true, except: '')]
    public $date = '';

    #[Url(history: true, except: '')]
    public $category = '';

    #[Url(history: true, except: '')]
    public $audience = '';

    #[Url(history: true, except: '')]
    public $state = '';

    private CarbonImmutable $now;

    public function boot(): void
    {
        $this->now = CarbonImmutable::now();
    }

    public function mount(): void
    {
        $this->filters;
    }

    public function search(): void
    {
        $this->resetErrorBag();
        $this->filters;
    }

    /** @return array{location: list<string>, category: list<string>, audience: list<string>} */
    #[Computed]
    public function facets(): array
    {
        $query = Experience::query()->discoverableAt($this->now);

        return [
            'location' => $query->clone()->distinct()->orderBy('locality')->pluck('locality')->all(),
            'category' => $query->clone()->distinct()->orderBy('category')->pluck('category')->all(),
            'audience' => $query->clone()->distinct()->orderBy('audience')->pluck('audience')->all(),
        ];
    }

    /** @return array{location: ?string, date: ?string, category: ?string, audience: ?string, state: ?string} */
    #[Computed]
    public function filters(): array
    {
        $this->resetErrorBag();

        $facets = $this->facets;
        $location = $this->facetFilter('location', $facets['location']);
        $category = $this->facetFilter('category', $facets['category']);
        $audience = $this->facetFilter('audience', $facets['audience']);
        $date = $this->dateFilter();
        $state = $this->stateFilter();

        return compact('location', 'date', 'category', 'audience', 'state');
    }

    /** @return Collection<int, Experience> */
    #[Computed]
    public function results(): Collection
    {
        $filters = $this->filters;

        if ($this->getErrorBag()->isNotEmpty()) {
            return new Collection;
        }

        $query = Experience::query()->discoverableAt($this->now);

        if ($filters['location'] !== null) {
            $query->inLocality($filters['location']);
        }

        if ($filters['date'] !== null) {
            $query->onLocalDate($filters['date']);
        }

        if ($filters['category'] !== null) {
            $query->inCategory($filters['category']);
        }

        if ($filters['audience'] !== null) {
            $query->forAudience($filters['audience']);
        }

        if ($filters['state'] === 'active') {
            $query->activeAt($this->now);
        }

        if ($filters['state'] === 'upcoming') {
            $query->upcomingAt($this->now);
        }

        return $query->orderedForDiscovery()->get();
    }

    /** @param list<string> $options */
    private function facetFilter(string $field, array $options): ?string
    {
        $value = $this->valueFor($field);

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || ! in_array($value, $options, true)) {
            $this->addError($field, 'Choose a valid '.($field === 'location' ? 'locality' : $field).'.');

            return null;
        }

        return $value;
    }

    private function dateFilter(): ?string
    {
        $value = $this->valueFor('date');

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || ! $this->isIsoDate($value)) {
            $this->addError('date', 'Choose a valid date.');

            return null;
        }

        return $value;
    }

    private function stateFilter(): ?string
    {
        $value = $this->valueFor('state');

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || ! in_array($value, ['active', 'upcoming'], true)) {
            $this->addError('state', 'Choose an active or upcoming state.');

            return null;
        }

        return $value;
    }

    private function valueFor(string $field): mixed
    {
        $value = $this->{$field};

        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function isIsoDate(string $value): bool
    {
        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (InvalidFormatException) {
            return false;
        }

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}; ?>

<main>
    @php($filters = $this->filters)

    <h1>Discover experiences</h1>

    <form wire:submit="search">
        @if ($this->getErrorBag()->isNotEmpty())
            <div role="alert">Choose valid filters before viewing experiences.</div>
        @endif

        <flux:select wire:model="location" label="Locality">
            <flux:select.option value="">Any locality</flux:select.option>
            @foreach ($this->facets['location'] as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:input wire:model="date" type="date" label="Date" />
        <flux:select wire:model="category" label="Category">
            <flux:select.option value="">Any category</flux:select.option>
            @foreach ($this->facets['category'] as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model="audience" label="Audience">
            <flux:select.option value="">Any audience</flux:select.option>
            @foreach ($this->facets['audience'] as $option)
                <flux:select.option :value="$option">{{ $option }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model="state" label="State">
            <flux:select.option value="">Active and upcoming</flux:select.option>
            <flux:select.option value="active">Active</flux:select.option>
            <flux:select.option value="upcoming">Upcoming</flux:select.option>
        </flux:select>
        <flux:button type="submit" variant="primary">Find experiences</flux:button>
        <a href="{{ route('discover.index', absolute: false) }}">Clear filters</a>
    </form>

    @if ($this->getErrorBag()->isEmpty())
        <p role="status">{{ $this->results->count() }} experiences found.</p>

        <section aria-label="Discovery results">
            @foreach ($this->results as $experience)
                <article>
                    <h2>{{ $experience->title }}</h2>
                    <p>{{ $experience->locality }} · {{ $experience->category }} · {{ $experience->audience }}</p>
                    <p>
                        {{ $experience->starts_at->setTimezone($experience->timezone)->format('Y-m-d H:i') }}
                        to {{ $experience->ends_at->setTimezone($experience->timezone)->format('Y-m-d H:i') }}
                        ({{ $experience->timezone }})
                    </p>
                    <p>{{ $experience->starts_at->gt($this->now) ? 'Upcoming' : 'Active' }}</p>
                </article>
            @endforeach
        </section>
    @endif
</main>
