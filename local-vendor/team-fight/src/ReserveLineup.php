<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Squad;

/**
 * A lineup as played with its reserve records (§43): on a Squad, X plays for Y. X stands in the Squad's slots instead of
 * Y, and may also keep a slot on a lower Squad.
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
     * The records that count, each with the categories X plays on the Squad: X stands on the Squad and Y doesn't. Stale
     * records are left out.
     *
     * @return list<array{squadIndex: int, reserve: Player, replaced: Player, categories: list<Category>}>
     */
    public function records(): array
    {
        $records = [];
        foreach ($this->squads as $squadIndex => $squad) {
            foreach ($squad->reserves as $record) {
                $categories = $this->categoriesOf($squad, $record->reserveRefId);
                if ($categories === [] || $this->categoriesOf($squad, $record->replaced->refId) !== []) {
                    continue;
                }
                $reserve = $this->playerIn($categories[0], $record->reserveRefId);
                $records[] = ['squadIndex' => $squadIndex, 'reserve' => $reserve, 'replaced' => $record->replaced, 'categories' => $categories];
            }
        }

        return $records;
    }

    /**
     * @return list<Category>
     */
    private function categoriesOf(Squad $squad, string $refId): array
    {
        return array_values(array_filter($squad->categories, fn (Category $category) => $this->playerIn($category, $refId) !== null));
    }

    private function playerIn(Category $category, string $refId): ?Player
    {
        foreach ($category->players as $player) {
            if ($player->refId === $refId) {
                return $player;
            }
        }

        return null;
    }
}
