<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\Squad;
use App\Models\SquadReserve;
use FlyCompany\TeamFight\ReserveManager;

class AddSquadReserve
{
    public function __construct(
        private readonly ReserveManager $reserveManager
    ) {}

    /**
     * @param  array{squadId: string, scenarioId?: string|null, reserveRefId: string, replacedRefId: string}  $args
     */
    public function __invoke(mixed $rootValue, array $args): SquadReserve
    {
        /** @var Squad $squad */
        $squad = Squad::query()->findOrFail($args['squadId']);
        $scenarioId = isset($args['scenarioId']) ? (int) $args['scenarioId'] : null;

        return $this->reserveManager->add($squad, $scenarioId, $args['reserveRefId'], $args['replacedRefId']);
    }
}
