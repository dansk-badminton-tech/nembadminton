<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;

/**
 * A lineup with its reserve records (§43): on a Squad, X from a lower Squad plays for Y. The lineup as played has X in
 * every one of Y's category slots on that Squad, while X keeps their own slot on the lower Squad.
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
     * The records that count: Y stands on the record's Squad and X on a lower Squad. Stale records are left out.
     *
     * @return list<array{squadIndex: int, reserve: Player, replaced: Player}>
     */
    public function records(): array
    {
        $records = [];
        foreach ($this->squads as $squadIndex => $squad) {
            foreach ($squad->reserves as $record) {
                $replaced = $this->playerOn($squad, $record->replacedRefId);
                $reserve = $this->playerBelow($squadIndex, $record->reserveRefId);
                if ($replaced === null || $reserve === null || $this->standsOnOrAbove($squadIndex, $record->reserveRefId)) {
                    continue;
                }
                $records[] = ['squadIndex' => $squadIndex, 'reserve' => $reserve, 'replaced' => $replaced];
            }
        }

        return $records;
    }

    /**
     * The lineup as played. The given Squads are not changed.
     *
     * @return list<Squad>
     */
    public function asPlayed(): array
    {
        $squads = array_map(function (Squad $squad) {
            $copy = clone $squad;
            $copy->categories = array_map(fn (Category $category) => clone $category, $squad->categories);

            return $copy;
        }, $this->squads);

        foreach ($this->records() as $record) {
            foreach ($squads[$record['squadIndex']]->categories as $category) {
                $category->players = array_map(
                    fn (Player $player) => $player->refId === $record['replaced']->refId ? $record['reserve'] : $player,
                    $category->players,
                );
            }
        }

        return $squads;
    }

    /**
     * The members placed on Squads lower than the given one, with the first Squad each stands on.
     *
     * @return list<Player>
     */
    public function playersBelow(int $squadIndex): array
    {
        $players = [];
        foreach (array_slice($this->squads, $squadIndex + 1) as $squad) {
            foreach ($squad->categories as $category) {
                foreach ($category->players as $player) {
                    $players[$player->refId] ??= $player;
                }
            }
        }

        return array_values($players);
    }

    private function playerBelow(int $squadIndex, string $refId): ?Player
    {
        foreach ($this->playersBelow($squadIndex) as $player) {
            if ($player->refId === $refId) {
                return $player;
            }
        }

        return null;
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
