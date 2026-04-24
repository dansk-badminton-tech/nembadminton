<?php

namespace Database\Factories;

use App\Models\TeamRound;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamRound>
 */
class TeamRoundFactory extends Factory
{
    protected $model = TeamRound::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'game_date' => $this->faker->date(),
            'version' => $this->faker->date(),
            'round' => $this->faker->numberBetween(1, 10),
        ];
    }
}
