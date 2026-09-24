<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\Export;

use App\Models\TeamRound;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Exporter
{
    public function exportToCSV(TeamRound $team, bool $includeCategories = true): string
    {
        $csv = [];
        $officialScenarioId = $team->officialScenario?->id;
        foreach ($team->squads as $index => $squad) {
            $i = $index + 1;
            $csv[] = '"'."Hold $i".'"';
            $seenPlayers = [];
            foreach ($this->officialCategories($squad->categories, $officialScenarioId) as $category) {
                foreach ($category->players as $playerIndex => $player) {
                    if (! $includeCategories) {
                        $identifier = (string) ($player->member_ref_id ?: $player->name);
                        if ($identifier !== '') {
                            if (isset($seenPlayers[$identifier])) {
                                continue;
                            }
                            $seenPlayers[$identifier] = true;
                        }
                    }

                    $data = [];
                    if ($includeCategories) {
                        if ($playerIndex === 0) {
                            $data[] = '"'.$category->name.'"';
                        } else {
                            $data[] = '';
                        }
                    }
                    $data[] = '"'.$player->name.'"';
                    $csv[] = implode(',', $data);
                }
            }
            $csv[] = '';
        }

        return implode(PHP_EOL, $csv);
    }

    /**
     * Only the official lineup is exported. Mirrors ScenarioManager::getOfficialCategories: team rounds
     * whose official scenario has no categories fall back to the legacy categories without a scenario.
     */
    private function officialCategories(Collection $categories, ?int $officialScenarioId): Collection
    {
        if ($officialScenarioId !== null) {
            $official = $categories->where('team_round_scenario_id', $officialScenarioId);
            if ($official->isNotEmpty()) {
                return $official;
            }
        }

        return $categories->whereNull('team_round_scenario_id');
    }
}
