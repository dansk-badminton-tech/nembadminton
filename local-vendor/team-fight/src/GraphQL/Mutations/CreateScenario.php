<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use FlyCompany\TeamFight\ScenarioManager;

class CreateScenario
{
    public function __construct(
        private readonly ScenarioManager $scenarioManager
    ) {}

    public function __invoke($rootValue, array $args): TeamRoundScenario
    {
        /** @var TeamRound $teamRound */
        $teamRound = TeamRound::query()->findOrFail($args['teamRoundId']);

        return $this->scenarioManager->createScenario($teamRound, (string) $args['name']);
    }
}
