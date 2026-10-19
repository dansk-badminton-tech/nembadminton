<?php

declare(strict_types=1);

namespace App\SeasonHistory;

/**
 * How a player on a Paper Squad breaks the paper-team rule (§38 stk. 7).
 */
final readonly class PaperSquadBreach
{
    /**
     * @param  list<string>  $unplayedCategories  Categories the player stands in without having played them this season, in the order HS, DS, HD, DD, MD.
     * @param  int  $matchCount  Earlier team matches this season on the Paper Squad's Team or a lower one.
     * @param  int  $requiredMatchCount  The earlier team matches the rule needs in the TeamRound's phase.
     * @param  list<RoundLineup>  $uncountedRounds  Earlier TeamRounds the player stands in without a Squad for the Paper Squad's Team, so they couldn't count. Empty when the player has enough matches.
     */
    public function __construct(
        public array $unplayedCategories,
        public int $matchCount,
        public int $requiredMatchCount,
        public array $uncountedRounds,
    ) {}
}
