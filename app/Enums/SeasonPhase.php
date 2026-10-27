<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * A Squad's Season Phase, derived from round, tier and an explicit Match Kind where ambiguous.
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
     * Never infer phase from the date (§45). Missing round or ambiguous Liga Match Kind gives null.
     */
    public static function fromRound(?int $round, ?string $tier, ?MatchKind $matchKind = null): ?self
    {
        if ($round === null || $round < 1) {
            return null;
        }
        if ($round === self::QUALIFICATION_ROUND) {
            if (CanonicalTournamentTier::tryFromRawName($tier) !== CanonicalTournamentTier::BADMINTONLIGAEN) {
                return self::KVALIFIKATION;
            }

            return match ($matchKind) {
                MatchKind::MEDAL_FINAL => self::SLUTSPIL,
                MatchKind::QUALIFICATION => self::KVALIFIKATION,
                null => null,
            };
        }

        $lastGroupStageRound = CanonicalTournamentTier::tryFromRawName($tier) === CanonicalTournamentTier::BADMINTONLIGAEN
            ? self::LAST_GROUP_STAGE_ROUND_BADMINTONLIGAEN
            : self::LAST_GROUP_STAGE_ROUND;

        return $round <= $lastGroupStageRound ? self::GRUNDSPIL : self::SLUTSPIL;
    }
}
