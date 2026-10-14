<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * The Official Lineup of one TeamRound: its Squads in order and a ladder of CategorySlots per category.
 */
final readonly class RoundLineup
{
    /**
     * @param  list<LineupSquad>  $squads  In Squad order, highest team first.
     * @param  array<string, list<CategorySlot>>  $ladders  Keyed by category; each category is its own ladder.
     */
    public function __construct(
        public string $teamRoundId,
        public string $name,
        public ?int $round,
        public ?string $gameDate,
        public array $squads,
        public array $ladders,
    ) {}

    /**
     * @return list<CategorySlot>
     */
    public function ladder(string $category): array
    {
        return $this->ladders[$category] ?? [];
    }
}
