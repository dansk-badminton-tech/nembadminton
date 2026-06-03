<?php

namespace Database\Factories;

use App\Models\Clubhouse;
use App\Models\Season;
use App\Models\Team;
use App\Models\TournamentTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'group_name' => null,
            'tier_id' => null,
            'custom_tier_name' => null,
            'clubhouse_id' => Clubhouse::factory(),
            'season_id' => fn () => Season::query()->firstOrCreate(
                ['id' => 2026],
                ['season_name' => '2026/2027']
            )->id,
        ];
    }

    public function withTier(TournamentTier $tier): self
    {
        return $this->state(fn () => [
            'tier_id' => $tier->id,
            'custom_tier_name' => null,
        ]);
    }

    public function withCustomTier(string $name): self
    {
        return $this->state(fn () => [
            'tier_id' => null,
            'custom_tier_name' => $name,
        ]);
    }
}
