<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * A Reserve Player in a TeamRound's Official Lineup: they played for $replacedName on the Squad at $squadPosition.
 */
final readonly class RoundReserve
{
    /**
     * @param  int  $squadPosition  1-based place of the Squad they were a reserve on.
     */
    public function __construct(
        public string $refId,
        public string $name,
        public string $replacedName,
        public int $squadPosition,
    ) {}
}
