<?php

declare(strict_types=1);

namespace App\Util;

use App\Enums\TournamentPhaseType;

class TournamentStructureMapper
{
    public static function seasonNameFromSeasonId(int $seasonId): string
    {
        return $seasonId . '/' . ($seasonId + 1);
    }

    public static function normalizeDivisionName(?string $divisionName): ?string
    {
        $normalized = self::normalizeText($divisionName);
        if ($normalized === null) {
            return null;
        }

        // Convert names like "1. division (grundspil)" -> "1. division"
        $normalized = (string) preg_replace('/\s*\([^)]*\)\s*$/u', '', $normalized);

        return self::normalizeText($normalized);
    }

    public static function normalizeGroupName(?string $groupName): ?string
    {
        return self::normalizeText($groupName);
    }

    public static function rankLevelFromTierName(?string $tierName): ?int
    {
        if ($tierName === null) {
            return null;
        }

        $tier = mb_strtolower(trim($tierName));

        if ($tier === 'badmintonligaen') {
            return 1;
        }
        if (preg_match('/^1\.\s*division$/u', $tier) === 1) {
            return 2;
        }
        if (preg_match('/^2\.\s*division$/u', $tier) === 1) {
            return 3;
        }
        if (preg_match('/^3\.\s*division$/u', $tier) === 1) {
            return 4;
        }
        if ($tier === 'danmarksserien') {
            return 5;
        }

        return null;
    }

    public static function phaseTypeFromGroupName(?string $groupName): TournamentPhaseType
    {
        $normalized = mb_strtolower(self::normalizeGroupName($groupName) ?? '');

        if ($normalized === '') {
            return TournamentPhaseType::OTHER;
        }

        if (self::containsAny($normalized, ['oversidder', 'papirhold'])) {
            return TournamentPhaseType::BYES_PAPER_TEAM;
        }

        if (self::containsAny($normalized, ['kvartfinal', 'semifinal', 'finale', 'guldkamp', 'bronzekamp'])) {
            return TournamentPhaseType::PLAYOFF;
        }

        if (self::containsAny($normalized, ['kvalifikation', 'kvalifikations', 'kvalkampe', 'kvalkamp', 'oprykning', 'nedrykning'])) {
            return TournamentPhaseType::PROMOTION_RELEGATION;
        }

        if (self::containsAny($normalized, ['grundspil', 'pulje'])) {
            return TournamentPhaseType::REGULAR_SEASON;
        }

        return TournamentPhaseType::OTHER;
    }

    private static function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace(["\t", "\n", "\r"], ' ', $value);
        $value = (string) preg_replace('/\s+/u', ' ', trim($value));

        return $value === '' ? null : $value;
    }

    private static function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }
}
