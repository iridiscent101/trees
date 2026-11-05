<?php

namespace Database\Factories;

use App\Enums\TreeCondition;
use App\Enums\TreeStatus;
use App\Enums\VerificationStatus;
use App\Models\CampusArea;
use App\Models\Species;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Tree> */
class TreeFactory extends Factory
{
    protected $model = Tree::class;

    public function definition(): array
    {
        return [
            'species_id' => Species::factory(), 'campus_area_id' => CampusArea::factory(),
            'descriptive_location' => fake()->streetName(), 'nearby_landmark' => fake()->optional()->company(),
            'latitude' => fake()->latitude(16.58, 16.62), 'longitude' => fake()->longitude(121.57, 121.62),
            'trunk_diameter_cm' => fake()->randomFloat(2, 15, 150), 'estimated_height_m' => fake()->randomFloat(2, 3, 35),
            'estimated_age_years' => fake()->numberBetween(3, 100), 'current_condition' => fake()->randomElement(TreeCondition::cases()),
            'status' => TreeStatus::Existing, 'verification_status' => VerificationStatus::Pending,
            'recorded_by' => User::factory(), 'is_featured' => false,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['verification_status' => VerificationStatus::Verified, 'verified_at' => now(), 'published_at' => now()]);
    }
}
