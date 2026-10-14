<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The phase of the season a TeamRound belongs to, derived from its round number and the Squad's tier.
 *
 * Not to be confused with TournamentPhaseType, which describes groups (puljer) from badmintonplayer.dk.
 */
enum SeasonPhase: string
{
    case GRUNDSPIL = 'grundspil';
    case SLUTSPIL = 'slutspil';
    case KVALIFIKATION = 'kvalifikation';

    /** §27 stk. 1: the qualification round. */
    private const QUALIFICATION_ROUND = 12;

    /** §9 stk. 1: the last round of the group stage. */
    private const LAST_GROUP_STAGE_ROUND = 7;

    private const LAST_GROUP_STAGE_ROUND_BADMINTONLIGAEN = 9;

    /**
     * The phase is decided by the round number, not the date (§45). A missing round gives an unknown phase (null).
     */
    public static function fromRound(?int $round, ?string $tier): ?self
    {
        if ($round === null || $round < 1) {
            return null;
        }
        if ($round === self::QUALIFICATION_ROUND) {
            return self::KVALIFIKATION;
        }

        $lastGroupStageRound = CanonicalTournamentTier::tryFromRawName($tier) === CanonicalTournamentTier::BADMINTONLIGAEN
            ? self::LAST_GROUP_STAGE_ROUND_BADMINTONLIGAEN
            : self::LAST_GROUP_STAGE_ROUND;

        return $round <= $lastGroupStageRound ? self::GRUNDSPIL : self::SLUTSPIL;
    }
}
