<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\CanonicalTournamentTier;

/**
 * The Playoff Participation Requirement (§32 stk. 1 a–c), checked against the Season History before a TeamRound: a
 * player has stood in at least 4 (Badmintonligaen) or 3 (1. division) earlier team matches in the DH Tournament this
 * season, and in at least one of them, not on a Paper Squad, played no later than 20 October.
 *
 * A placement on a Squad whose tier is unknown, or that has no date, isn't guessed: the result is CANNOT_VERIFY when
 * counting it could turn an unmet requirement into a met one.
 */
final readonly class PlayoffParticipationRequirement
{
    /** §32 stk. 1 c: month and day of the deadline in the Season's start year. */
    private const DEADLINE = '10-20';

    /**
     * @param  SeasonHistory  $earlier  The Season History before the TeamRound, without the TeamRound itself.
     * @param  int  $requiredMatchCount  See requiredMatchCount().
     */
    public function __construct(
        private SeasonHistory $earlier,
        private int $seasonStartYear,
        private int $requiredMatchCount,
    ) {}

    /**
     * The earlier team matches a player on a Squad of the tier needs in the playoffs and qualification; 0 when the
     * requirement doesn't cover the tier.
     */
    public static function requiredMatchCount(CanonicalTournamentTier $tier): int
    {
        return match ($tier) {
            CanonicalTournamentTier::BADMINTONLIGAEN => 4,
            CanonicalTournamentTier::DIVISION_1 => 3,
            default => 0,
        };
    }

    public function check(string $refId): PlayoffParticipation
    {
        $placements = $this->earlier->placementsFor($refId);
        $matchCount = $this->earlier->dhTournamentMatchCount($refId);
        $unknownTier = array_filter($placements, fn (Placement $placement) => self::tier($placement) === null);
        $deadline = $this->seasonStartYear.'-'.self::DEADLINE;
        // A Paper Squad's match isn't played (§38 stk. 7), so it never meets the deadline.
        $byDeadline = array_filter($placements, fn (Placement $placement) => ! $placement->isPaperSquad()
            && self::tier($placement)?->inDhTournament() !== false
            && ($placement->squad->matchDate === null || $placement->squad->matchDate <= $deadline));

        $meetsCount = match (true) {
            $matchCount >= $this->requiredMatchCount => true,
            $matchCount + count($unknownTier) >= $this->requiredMatchCount => null,
            default => false,
        };
        $playedByDeadline = match (true) {
            array_filter($byDeadline, fn (Placement $placement) => self::tier($placement) !== null && $placement->squad->matchDate !== null) !== [] => true,
            $byDeadline !== [] => null,
            default => false,
        };

        if ($meetsCount === false || $playedByDeadline === false) {
            return new PlayoffParticipation('REQUIREMENT_NOT_MET', [], $matchCount, $playedByDeadline, []);
        }
        if ($meetsCount && $playedByDeadline) {
            return new PlayoffParticipation('SATISFIED', [], $matchCount, true, []);
        }

        $open = array_merge($meetsCount === null ? $unknownTier : [], $playedByDeadline === null ? $byDeadline : []);
        $reasons = array_filter([
            'UNKNOWN_HISTORY_TIER' => array_filter($open, fn (Placement $placement) => self::tier($placement) === null) !== [],
            'MISSING_HISTORY_DATE' => array_filter($open, fn (Placement $placement) => $placement->squad->matchDate === null) !== [],
        ]);
        $openRoundIds = array_map(fn (Placement $placement) => $placement->round->teamRoundId, $open);
        $uncountedRounds = array_values(array_filter(
            $this->earlier->rounds(),
            fn (RoundLineup $round) => in_array($round->teamRoundId, $openRoundIds, true),
        ));

        return new PlayoffParticipation('CANNOT_VERIFY', array_keys($reasons), $matchCount, $playedByDeadline, $uncountedRounds);
    }

    private static function tier(Placement $placement): ?CanonicalTournamentTier
    {
        return CanonicalTournamentTier::tryFromRawName($placement->squad->tier);
    }
}
