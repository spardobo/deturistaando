<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Participant> */
#[UseModel(Participant::class)]
class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'experience_id' => Experience::factory(),
            'name' => 'Market stand',
            'created_by_type' => 'system',
            'created_by_id' => null,
            'updated_by_type' => null,
            'updated_by_id' => null,
            'deleted_by_type' => null,
            'deleted_by_id' => null,
        ];
    }
}
