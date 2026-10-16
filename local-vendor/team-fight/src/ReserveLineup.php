<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;

/**
 * A lineup with its reserve records (§43): on a Squad, X from a lower Squad plays for Y.
 */
class ReserveLineup
{
    /**
     * @var list<Squad>
     */
    private array $squads;

    /**
     * @param  Squad[]  $squads  In Squad order, highest first.
     */
    public function __construct(array $squads)
    {
        $this->squads = array_values($squads);
    }

    /**
     * The records that count: Y stands on the record's Squad and X first stands on a lower Squad. Stale records are
     * left out.
     *
     * @return list<array{squadIndex: int, reserve: Player, replaced: Player}>
     */
    public function records(): array
    {
        $records = [];
        foreach ($this->squads as $squadIndex => $squad) {
            foreach ($squad->reserves as $record) {
                $replaced = $this->playerOn($squad, $record->replacedRefId);
                [$reserveIndex, $reserve] = $this->firstPlacement($record->reserveRefId);
                if ($replaced === null || $reserve === null || $reserveIndex <= $squadIndex) {
                    continue;
                }
                $records[] = ['squadIndex' => $squadIndex, 'reserve' => $reserve, 'replaced' => $replaced];
            }
        }

        return $records;
    }

    /**
     * @return array{int, Player|null} The first Squad the Member stands on and their player there, or [-1, null].
     */
    private function firstPlacement(string $refId): array
    {
        foreach ($this->squads as $index => $squad) {
            $player = $this->playerOn($squad, $refId);
            if ($player !== null) {
                return [$index, $player];
            }
        }

        return [-1, null];
    }

    private function playerOn(Squad $squad, string $refId): ?Player
    {
        foreach ($squad->categories as $category) {
            foreach ($category->players as $player) {
                if ($player->refId === $refId) {
                    return $player;
                }
            }
        }

        return null;
    }
}
