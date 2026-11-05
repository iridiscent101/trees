<?php

namespace Database\Factories;

use App\Enums\ConservationStatus;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Species> */
class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        return ['common_name' => ucfirst(fake()->unique()->word()), 'scientific_name' => ucfirst(fake()->unique()->word()).' '.fake()->word(), 'family_name' => ucfirst(fake()->word()).'aceae', 'conservation_status' => fake()->randomElement(ConservationStatus::cases()), 'description' => fake()->paragraph(), 'is_active' => true];
    }
}
