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
     * Create a new scenario for a team round, cloning either a source scenario or the current official lineup into it.
     */
    public function createScenario(TeamRound $teamRound, string $name, ?int $sourceScenarioId = null): TeamRoundScenario
    {
        return DB::transaction(function () use ($teamRound, $name, $sourceScenarioId) {
            if ($sourceScenarioId !== null) {
                $teamRound->scenarios()->findOrFail($sourceScenarioId);
            }

            /** @var TeamRoundScenario $scenario */
            $scenario = $teamRound->scenarios()->create([
                'name' => $name,
                'is_official' => false,
            ]);

            if ($sourceScenarioId !== null) {
                $this->cloneScenarioLineupToScenario($teamRound, $sourceScenarioId, $scenario);
            } else {
                $this->cloneOfficialLineupToScenario($teamRound, $scenario);
            }

            return $scenario;
        });
    }

    /**
     * Promotes a draft scenario to be the official lineup for the team round.
     * Atomically swaps is_official flag and preserves the outgoing official lineup as a draft scenario without deleting records.
     */
    public function promoteScenario(TeamRoundScenario $targetScenario): TeamRound
    {
        return DB::transaction(function () use ($targetScenario) {
            /** @var TeamRound $teamRound */
            $teamRound = $targetScenario->teamRound()->lockForUpdate()->firstOrFail();
            $squadIds = $teamRound->squads()->pluck('id');

            // 1. Identify or preserve the outgoing official lineup
            $currentOfficialScenario = $teamRound->officialScenario;

            if ($currentOfficialScenario === null) {
                $hasNullCategories = SquadCategory::query()
                    ->whereIn('squad_id', $squadIds)
                    ->whereNull('team_round_scenario_id')
                    ->exists();

                if ($hasNullCategories) {
                    /** @var TeamRoundScenario $preservedDraft */
                    $preservedDraft = $teamRound->scenarios()->create([
                        'name' => $teamRound->name ?? 'Oprindelig opstilling',
                        'is_official' => false,
                    ]);

                    SquadCategory::query()
                        ->whereIn('squad_id', $squadIds)
                        ->whereNull('team_round_scenario_id')
                        ->update(['team_round_scenario_id' => $preservedDraft->id]);
                }
            }

            // 2. Demote any other official scenarios on this team round
            $teamRound->scenarios()
                ->where('id', '!=', $targetScenario->id)
                ->where('is_official', true)
                ->update(['is_official' => false]);

            // 3. Promote target scenario
            $targetScenario->update(['is_official' => true]);

            return $teamRound->refresh();
        });
    }

    /**
     * Get categories for a squad scoped by scenario ID or defaulting to official lineup.
     *
     * @return Collection<int, SquadCategory>
     */
    public function getCategories(Squad $squad, ?int $scenarioId = null): Collection
    {
        if ($scenarioId !== null) {
            return $squad->categories()
                ->where('team_round_scenario_id', $scenarioId)
                ->with(['players.points'])
                ->get();
        }

        $officialScenario = $squad->teamRound?->officialScenario;

        return $this->getOfficialCategories($squad, $officialScenario);
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
     * Clones a specific scenario's lineup across all squads in the team round into the target scenario.
     */
    public function cloneScenarioLineupToScenario(TeamRound $teamRound, int $sourceScenarioId, TeamRoundScenario $targetScenario): void
    {
        foreach ($teamRound->squads as $squad) {
            $categories = $this->getCategories($squad, $sourceScenarioId);
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
     * Renames a scenario.
     */
    public function renameScenario(TeamRoundScenario $scenario, string $name): TeamRoundScenario
    {
        $scenario->update([
            'name' => $name,
        ]);

        return $scenario->refresh();
    }

    /**
     * Deletes a draft scenario and all its associated categories, players, and points.
     * Prevents deleting the official lineup.
     */
    public function deleteScenario(TeamRoundScenario $scenario): bool
    {
        if ($scenario->is_official) {
            throw new CannotDeleteOfficialScenarioException('Cannot delete the official lineup.');
        }

        return DB::transaction(function () use ($scenario) {
            return (bool) $scenario->delete();
        });
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
