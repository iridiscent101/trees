<?php

namespace Database\Factories;

use App\Models\CampusArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<CampusArea> */
class CampusAreaFactory extends Factory
{
    protected $model = CampusArea::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true).' Area';

        return ['code' => strtoupper(fake()->unique()->bothify('AR-##??')), 'name' => $name, 'slug' => Str::slug($name), 'description' => fake()->sentence(), 'is_active' => true];
    }
}
