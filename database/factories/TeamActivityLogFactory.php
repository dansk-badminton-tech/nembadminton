<?php

namespace Database\Factories;

use App\Enums\ActivityAction;
use App\Models\TeamActivityLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamActivityLog>
 */
class TeamActivityLogFactory extends Factory
{
    protected $model = TeamActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'action' => ActivityAction::TEAM_PUBLISH,
            'message' => $this->faker->sentence,
            'metadata' => [],
        ];
    }
}
