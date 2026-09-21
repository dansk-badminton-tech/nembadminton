<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use FlyCompany\TeamFight\ScenarioManager;

class PromoteScenario
{
    public function __construct(
        private readonly ScenarioManager $scenarioManager
    ) {}

    public function __invoke($rootValue, array $args): TeamRound
    {
        /** @var TeamRoundScenario $scenario */
        $scenario = TeamRoundScenario::query()->findOrFail($args['scenarioId']);

        return $this->scenarioManager->promoteScenario($scenario);
    }
}
