<?php

namespace Modules\Layanan\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LayananFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Layanan\Models\Layanan::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'image' => null,
            'urutan' => fake()->numberBetween(1, 10),
            'status' => 1,
        ];
    }
}

