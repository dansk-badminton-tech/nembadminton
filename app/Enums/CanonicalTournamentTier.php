<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Canonical badminton team-tournament tier names.
 *
 * Sourced by scraping badmintonplayer.dk league standings for all 25
 * organizers (Badminton Danmark + 8 Kreds + DGI national + 15 DGI units)
 * for age group SEN, season 2025/2026.
 *
 * Normalization rules:
 *  - Slutspil / playoff variants are dropped (they are phases of a tier,
 *    not separate tiers).
 *  - Identical tiers exposed in multiple format variants (e.g.
 *    "Senior A (4 Spillere - single/double)" and
 *    "Senior A (4+2) DOUBLE") collapse into a single case.
 *  - Regional naming conventions for the same level are merged
 *    (KBH "1. Serie" + others "Serie 1" => SERIE_1).
 *
 * The string value is the Danish display label used in the UI.
 */
enum CanonicalTournamentTier: string
{
    // ---------- National ladder (Badminton Danmark) ----------
    case BADMINTONLIGAEN = 'Badmintonligaen';
    case DIVISION_1 = '1. division';
    case DIVISION_2 = '2. division';
    case DIVISION_3 = '3. division';
    case DANMARKSSERIEN = 'Danmarksserien';

    // ---------- Regional top series ----------
    case KOEBENHAVNSSERIEN = 'Københavnsserien';
    case LF_SERIEN = 'LF-serien';
    case SJAELLANDSSERIEN = 'Sjællandsserien';
    case KREDSSERIEN_VEST = 'Kredsserien Vest';

    // ---------- Numbered series ladder ----------
    case SERIE_1 = 'Serie 1';
    case SERIE_2 = 'Serie 2';
    case SERIE_3 = 'Serie 3';
    case SERIE_4 = 'Serie 4';
//    case SERIE_31 = '31. Serie';
//    case SERIE_32 = '32. Serie';

    // ---------- Letter-grade Serie (Nordjylland) ----------
    case SERIE_A = 'Serie A';
    case SERIE_B = 'Serie B';
    case SERIE_C = 'Serie C';
    case SERIE_D = 'Serie D';

    // ---------- DGI Senior hyggefjer ladder ----------
//    case SENIOR_A = 'Senior A';
//    case SENIOR_B = 'Senior B';
//    case SENIOR_C = 'Senior C';
//    case SENIOR_D = 'Senior D';

    // ---------- Fyn senior herre ----------
    #case SEN_HR_A = 'Sen Hr - A';
    #case SEN_HR_B = 'Sen Hr - B';
    #case SEN_HR_C = 'Sen Hr - C';
    #case SEN_4_2_B = 'Sen 4 + 2 B';

    // ---------- Format-only / qualification ----------
    //case KVAL_RAEKKEN = 'Kval-rækken 5+3';
    //case FORMAT_4_2 = '4+2';
    //case FORMAT_4_SPILLERE = '4 Spillere';
    //case SERIE_1_VEST_5_3 = 'Serie 1 Vest (5+3)';

    // ---------- LF herrehold ----------
    //case HERREHOLD_4_KAMPE = 'Herrehold 4 kampe';
    //case HERREHOLD_6_KAMPE = 'Herrehold 6 kampe';

    // ---------- Recreational ----------
    //case VOKSENFJER = 'VoksenFjer';

    // ---------- DM I HYGGEFJER (national hygge formats) ----------
//    case HYGGEFJER_4_2_DOUBLE_6 = 'DM I HYGGEFJER (4+2 DOUBLE-6 kampe)';
//    case HYGGEFJER_4_DOUBLE_4 = 'DM I HYGGEFJER (4 Sp. DOUBLE-4 kampe)/SEN+ (2+2)';
//    case HYGGEFJER_4_SINGLE_DOUBLE_5 = 'DM I HYGGEFJER (4 spillere SINGLE/DOUBLE-5 kampe)';
//    case HYGGEFJER_4_2_SINGLE_8 = 'DM I HYGGEFJER (4+2 m/SINGLE-8 kampe)';

    /**
     * Human-friendly Danish label used in autocompletes / dropdowns.
     */
    public function label(): string
    {
        return $this->value;
    }

    /**
     * Try to resolve a raw badmintonplayer.dk tier name to a canonical case.
     * Returns null when the name does not map to a known tier (e.g. slutspil
     * variants of unknown tiers, free-form club inventions, or tiers we have
     * deliberately not canonicalized yet).
     *
     * Handles normalizations:
     *  - Whitespace collapsing
     *  - Trailing "(oversidder-runde[r])" suffix
     *  - "* slutspil *" / "* - slutspil 2026" / "Øverste slutspil" / "Nederste slutspil" -> base tier
     *  - KBH postfix "1. Serie" -> "Serie 1"
     *  - "Senior X (...)" / "Serie X - ..." -> base grade
     *  - Case-insensitive match against canonical labels
     */
    public static function tryFromRawName(?string $name): ?self
    {
        if ($name === null) {
            return null;
        }

        $normalized = trim((string) preg_replace('/\s+/u', ' ', $name));
        if ($normalized === '') {
            return null;
        }

        // Direct value match.
        $direct = self::tryFrom($normalized);
        if ($direct !== null) {
            return $direct;
        }

        // Strip "(oversidder-runde[r])" suffixes.
        $normalized = (string) trim((string) preg_replace('/\s*\(oversidder-runder?\)\s*$/iu', '', $normalized));

        // Slutspil variants -> map back to base tier.
        $base = (string) preg_replace(
            '/\s*-\s*(øverste|nederste)\s+slutspil\s*$/iu',
            '',
            $normalized
        );
        $base = (string) preg_replace(
            '/\s*(-\s*)?slutspil(stider)?(\s*\d{4})?(\s*-\s*(oprykning|nedrykning|øverste\s+slutspil|nederste\s+slutspil))?\s*$/iu',
            '',
            $base
        );
        $base = trim($base);

        // Case-insensitive lookup since some BP names lowercase "spillere".
        foreach (self::cases() as $case) {
            if (mb_strtolower($case->value) === mb_strtolower($base)) {
                return $case;
            }
        }

        $direct = self::tryFrom($base);
        if ($direct !== null) {
            return $direct;
        }

        // KBH postfix style "1. Serie" -> "Serie 1".
        if (preg_match('/^(\d+)\.\s*Serie$/u', $base, $m) === 1) {
            $candidate = 'Serie ' . $m[1];
            $direct = self::tryFrom($candidate);
            if ($direct !== null) {
                return $direct;
            }
        }

        // DGI Senior grade with format suffix -> base grade.
        if (preg_match('/^Senior\s+([A-D])\b/u', $base, $m) === 1) {
            $direct = self::tryFrom('Senior ' . strtoupper($m[1]));
            if ($direct !== null) {
                return $direct;
            }
        }

        // "Serie A - Double" / "Serie A - Single + Double" -> base grade.
        if (preg_match('/^Serie\s+([A-D])\s*-/u', $base, $m) === 1) {
            $direct = self::tryFrom('Serie ' . strtoupper($m[1]));
            if ($direct !== null) {
                return $direct;
            }
        }

        return null;
    }
}
