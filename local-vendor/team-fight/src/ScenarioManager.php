<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ScenarioManager
{
    /**
     * Create a new scenario for a team round, cloning the current official lineup into it.
     */
    public function createScenario(TeamRound $teamRound, string $name): TeamRoundScenario
    {
        return DB::transaction(function () use ($teamRound, $name) {
            /** @var TeamRoundScenario $scenario */
            $scenario = $teamRound->scenarios()->create([
                'name' => $name,
                'is_official' => false,
            ]);

            $this->cloneOfficialLineupToScenario($teamRound, $scenario);

            return $scenario;
        });
    }

    /**
     * Clones the official lineup across all squads in the team round into the target scenario.
     */
    public function cloneOfficialLineupToScenario(TeamRound $teamRound, TeamRoundScenario $targetScenario): void
    {
        $officialScenario = $teamRound->officialScenario;

        foreach ($teamRound->squads as $squad) {
            $categories = $this->getOfficialCategories($squad, $officialScenario);
            foreach ($categories as $category) {
                $this->cloneCategory($category, $targetScenario->id);
            }
        }
    }

    /**
     * Get the official categories for a squad.
     * Falls back to default categories (team_round_scenario_id IS NULL) if none found under official scenario.
     *
     * @return Collection<int, SquadCategory>
     */
    public function getOfficialCategories(Squad $squad, ?TeamRoundScenario $officialScenario): Collection
    {
        if ($officialScenario !== null) {
            $categories = $squad->categories()
                ->where('team_round_scenario_id', $officialScenario->id)
                ->with(['players.points'])
                ->get();

            if ($categories->isNotEmpty()) {
                return $categories;
            }
        }

        return $squad->categories()
            ->whereNull('team_round_scenario_id')
            ->with(['players.points'])
            ->get();
    }

    /**
     * Clones a single category, its players, and points to a target scenario.
     */
    public function cloneCategory(SquadCategory $sourceCategory, int $targetScenarioId): SquadCategory
    {
        /** @var SquadCategory $newCategory */
        $newCategory = $sourceCategory->replicate();
        $newCategory->team_round_scenario_id = $targetScenarioId;
        $newCategory->save();

        foreach ($sourceCategory->players as $sourcePlayer) {
            $this->clonePlayer($sourcePlayer, $newCategory);
        }

        return $newCategory;
    }

    /**
     * Clones a player and all their points into a target category.
     */
    public function clonePlayer(SquadMember $sourcePlayer, SquadCategory $targetCategory): SquadMember
    {
        /** @var SquadMember $newPlayer */
        $newPlayer = $sourcePlayer->replicate();
        $targetCategory->players()->save($newPlayer);

        foreach ($sourcePlayer->points as $point) {
            $newPoint = $point->replicate();
            $newPlayer->points()->save($newPoint);
        }

        return $newPlayer;
    }
}
