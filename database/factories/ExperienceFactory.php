<?php

namespace Database\Factories;

use App\Enums\ExperienceStatus;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Experience> */
#[UseModel(Experience::class)]
class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'Market morning',
            'locality' => 'Madrid',
            'category' => 'Food',
            'audience' => 'Everyone',
            'status' => ExperienceStatus::Published,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'timezone' => 'Europe/Madrid',
            'created_by_type' => 'system',
            'created_by_public_id' => null,
            'updated_by_type' => null,
            'updated_by_public_id' => null,
            'deleted_by_type' => null,
            'deleted_by_public_id' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => ExperienceStatus::Published]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ExperienceStatus::Draft]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => ExperienceStatus::Cancelled]);
    }

    public function active(): static
    {
        return $this->state([
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state([
            'starts_at' => now()->addHour(),
            'ends_at' => now()->addHours(2),
        ]);
    }

    public function finished(): static
    {
        return $this->state([
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->subHour(),
        ]);
    }

    public function trashed(): static
    {
        return $this->afterCreating(fn (Experience $experience) => $experience->delete());
    }
}
