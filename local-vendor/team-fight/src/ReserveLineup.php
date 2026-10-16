<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;

/**
 * A lineup with its reserve records (§43): on a Squad, X plays for Y. X is an Available Member or stands on a lower
 * Squad.
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
     * The records that count: Y stands on the record's Squad and X doesn't stand on that Squad or a higher one. Stale
     * records are left out.
     *
     * @return list<array{squadIndex: int, reserve: Player, replaced: Player}>
     */
    public function records(): array
    {
        $records = [];
        foreach ($this->squads as $squadIndex => $squad) {
            foreach ($squad->reserves as $record) {
                $replaced = $this->playerOn($squad, $record->replacedRefId);
                if ($replaced === null || $this->standsOnOrAbove($squadIndex, $record->reserve->refId)) {
                    continue;
                }
                $records[] = ['squadIndex' => $squadIndex, 'reserve' => $record->reserve, 'replaced' => $replaced];
            }
        }

        return $records;
    }

    private function standsOnOrAbove(int $squadIndex, string $refId): bool
    {
        foreach (array_slice($this->squads, 0, $squadIndex + 1) as $squad) {
            if ($this->playerOn($squad, $refId) !== null) {
                return true;
            }
        }

        return false;
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
