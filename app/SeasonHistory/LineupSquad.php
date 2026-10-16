<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\SeasonPhase;

/**
 * A Squad in a TeamRound's Official Lineup.
 */
final readonly class LineupSquad
{
    /**
     * @param  int  $position  1-based place in the TeamRound's Squad order; 1 is the highest team.
     * @param  string|null  $teamName  The Team's name, or the Squad's own name when it has no Team.
     * @param  string|null  $tier  The Squad's effective tier (række).
     * @param  SeasonPhase|null  $phase  Null when the TeamRound has no round number.
     * @param  bool  $isPaperSquad  The Squad is a Paper Squad (papirhold). Its places count as played like any other.
     */
    public function __construct(
        public int $squadId,
        public int $position,
        public ?int $teamId,
        public ?string $teamName,
        public ?string $tier,
        public ?SeasonPhase $phase,
        public bool $isPaperSquad,
    ) {}
}
