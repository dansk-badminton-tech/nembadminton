<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\SeasonPhase;

/**
 * The paper-team rule (§38 stk. 7) for the players on a Paper Squad, checked against the Season History before its
 * TeamRound: a player stands only in categories they have played this season, and has stood on the Paper Squad's Team
 * or a lower one before, at least once in the group stage from round 2 and at least twice in the playoffs.
 *
 * The playoff match minimums for Liga and 1. division (§32 stk. 1 a–b) are not part of this rule.
 */
final readonly class PaperSquadRule
{
    private const FIRST_ROUND = 1;

    private const GROUP_STAGE_MATCHES = 1;

    private const PLAYOFF_MATCHES = 2;

    /**
     * @param  SeasonHistory  $earlier  The Season History before the Paper Squad's TeamRound, without the TeamRound itself.
     * @param  int  $round  The TeamRound's round number; it decides the phase.
     * @param  int  $teamId  The Paper Squad's Team.
     * @param  string|null  $tier  The Paper Squad's tier; the last group-stage round depends on it.
     */
    public function __construct(
        private SeasonHistory $earlier,
        private int $round,
        private int $teamId,
        private ?string $tier,
    ) {}

    /**
     * Null when the player meets the rule. Round 1 is exempt: there is no earlier TeamRound to have played in.
     *
     * @param  list<string>  $categories  The categories the player stands in on the Paper Squad.
     */
    public function check(string $refId, array $categories): ?PaperSquadBreach
    {
        if ($this->round <= self::FIRST_ROUND) {
            return null;
        }

        $played = $this->earlier->playedCategories($refId);
        $unplayed = array_values(array_filter(
            SeasonHistory::CATEGORIES,
            fn (string $category) => in_array($category, $categories, true) && ! in_array($category, $played, true),
        ));
        $matchCount = $this->earlier->matchCountOnSameOrLowerTeam($refId, $this->teamId);
        // The regulation names only the group stage and the playoffs; the qualification round counts as the playoffs.
        $requiredMatchCount = SeasonPhase::fromRound($this->round, $this->tier) === SeasonPhase::GRUNDSPIL
            ? self::GROUP_STAGE_MATCHES
            : self::PLAYOFF_MATCHES;

        if ($unplayed === [] && $matchCount >= $requiredMatchCount) {
            return null;
        }

        return new PaperSquadBreach($unplayed, $matchCount, $requiredMatchCount);
    }
}
