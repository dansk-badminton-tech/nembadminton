<?php

namespace Database\Factories;

use App\Models\Clubhouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clubhouse>
 */
class ClubhouseFactory extends Factory
{

    protected $model = Clubhouse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail()
        ];
    }
}
