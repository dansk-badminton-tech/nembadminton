<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Squad;
use App\Models\SquadReserve;
use FlyCompany\TeamFight\ReserveManager;
use Illuminate\Database\Eloquent\Collection;

/**
 * Squad.reserves: the Squad's reserve records in a Scenario, or in the official lineup.
 */
class SquadReservesResolver
{
    public function __construct(
        private readonly ReserveManager $reserveManager
    ) {}

    /**
     * @param  array{scenarioId?: string|int|null}  $args
     * @return Collection<int, SquadReserve>
     */
    public function __invoke(Squad $root, array $args): Collection
    {
        $scenarioId = isset($args['scenarioId']) ? (int) $args['scenarioId'] : null;

        return $this->reserveManager->reservesOf($root, $scenarioId);
    }
}
