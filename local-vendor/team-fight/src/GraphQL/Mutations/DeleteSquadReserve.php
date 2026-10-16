<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight\GraphQL\Mutations;

use App\Models\SquadReserve;
use FlyCompany\TeamFight\ReserveManager;

class DeleteSquadReserve
{
    public function __construct(
        private readonly ReserveManager $reserveManager
    ) {}

    /**
     * @param  array{id: string}  $args
     */
    public function __invoke(mixed $rootValue, array $args): SquadReserve
    {
        /** @var SquadReserve $record */
        $record = SquadReserve::query()->findOrFail($args['id']);

        return $this->reserveManager->remove($record);
    }
}
