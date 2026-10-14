<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * One place in a category's ladder across the Clubhouse's Squads in a TeamRound. Its rank is the Kategoriplads.
 * In HD, DD and MD both partners stand on the same slot.
 */
final readonly class CategorySlot
{
    /**
     * @param  string  $category  HS, DS, HD, DD or MD.
     * @param  int  $rank  1-based Kategoriplads within the category.
     * @param  string  $categoryName  E.g. "2. HS".
     * @param  list<SlotPlayer>  $players  May be fewer than the category holds; an empty slot keeps its rank.
     */
    public function __construct(
        public string $category,
        public int $rank,
        public string $categoryName,
        public LineupSquad $squad,
        public array $players,
    ) {}
}
