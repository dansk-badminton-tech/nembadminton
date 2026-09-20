<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Squad;
use FlyCompany\TeamFight\ScenarioManager;
use Illuminate\Database\Eloquent\Collection;

class SquadCategoriesResolver
{
    public function __construct(
        private readonly ScenarioManager $scenarioManager
    ) {}

    public function __invoke(Squad $root, array $args): Collection
    {
        $scenarioId = isset($args['scenarioId']) ? (int) $args['scenarioId'] : null;

        if ($scenarioId !== null) {
            return $root->categories()
                ->where('team_round_scenario_id', $scenarioId)
                ->with(['players.points'])
                ->get();
        }

        $officialScenario = $root->teamRound?->officialScenario;

        return $this->scenarioManager->getOfficialCategories($root, $officialScenario);
    }
}
