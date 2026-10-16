<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\SeasonPhase;

/**
 * Where a Member stands in the Official Lineup of one TeamRound on one Squad.
 * A Member on two Squads in the same TeamRound has two Placements.
 */
final readonly class Placement
{
    /**
     * @param  list<CategoryPlacement>  $categories  In the order HS, DS, HD, DD, MD.
     */
    public function __construct(
        public RoundLineup $round,
        public LineupSquad $squad,
        public array $categories,
    ) {}

    /**
     * Null when the TeamRound has no round number.
     */
    public function phase(): ?SeasonPhase
    {
        return $this->squad->phase;
    }

    /**
     * True when the Placement is on a Paper Squad (papirhold).
     */
    public function isPaperSquad(): bool
    {
        return $this->squad->isPaperSquad;
    }
}
