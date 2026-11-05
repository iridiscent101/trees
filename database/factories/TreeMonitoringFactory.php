<?php

namespace Database\Factories;

use App\Enums\TreeCondition;
use App\Enums\VerificationStatus;
use App\Models\Tree;
use App\Models\TreeMonitoring;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TreeMonitoring> */
class TreeMonitoringFactory extends Factory
{
    protected $model = TreeMonitoring::class;

    public function definition(): array
    {
        return ['tree_id' => Tree::factory(), 'observed_at' => fake()->dateTimeBetween('-1 year'), 'condition' => fake()->randomElement(TreeCondition::cases()), 'remarks' => fake()->sentence(), 'verification_status' => VerificationStatus::Pending, 'recorded_by' => User::factory()];
    }
}
