<?php

declare(strict_types=1);

namespace Tests\Unit\SeasonHistory;

use App\Enums\MatchKind;
use App\Enums\SeasonPhase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SeasonPhaseTest extends TestCase
{
    /**
     * @return array<string, array{int|null, string|null, SeasonPhase|null}>
     */
    public static function rounds(): array
    {
        return [
            'round 1 in 1. division is grundspil' => [1, '1. division', SeasonPhase::GRUNDSPIL],
            'round 7 in 1. division is grundspil' => [7, '1. division', SeasonPhase::GRUNDSPIL],
            'round 8 in 1. division is slutspil' => [8, '1. division', SeasonPhase::SLUTSPIL],
            'round 8 in Badmintonligaen is grundspil' => [8, 'Badmintonligaen', SeasonPhase::GRUNDSPIL],
            'round 9 in Badmintonligaen is grundspil' => [9, 'Badmintonligaen', SeasonPhase::GRUNDSPIL],
            'round 10 in Badmintonligaen is slutspil' => [10, 'Badmintonligaen', SeasonPhase::SLUTSPIL],
            'round 12 in 1. division is kvalifikation' => [12, '1. division', SeasonPhase::KVALIFIKATION],
            'round 12 in Badmintonligaen needs match kind' => [12, 'Badmintonligaen', null],
            'round 8 in a squad without a tier is slutspil' => [8, null, SeasonPhase::SLUTSPIL],
            'a missing round has an unknown phase' => [null, '1. division', null],
        ];
    }

    #[Test]
    #[DataProvider('rounds')]
    public function it_derives_the_phase_from_round_and_tier(?int $round, ?string $tier, ?SeasonPhase $expected): void
    {
        $this->assertSame($expected, SeasonPhase::fromRound($round, $tier));
    }

    #[Test]
    public function explicit_match_kind_only_disambiguates_liga_round_twelve(): void
    {
        $this->assertSame(SeasonPhase::SLUTSPIL, SeasonPhase::fromRound(12, 'Badmintonligaen', MatchKind::MEDAL_FINAL));
        $this->assertSame(SeasonPhase::KVALIFIKATION, SeasonPhase::fromRound(12, 'Badmintonligaen', MatchKind::QUALIFICATION));
        foreach (MatchKind::cases() as $kind) {
            foreach (self::rounds() as [$round, $tier, $expected]) {
                if ($round === 12 && $tier === 'Badmintonligaen') {
                    continue;
                }
                $this->assertSame($expected, SeasonPhase::fromRound($round, $tier, $kind));
            }
            $this->assertSame(SeasonPhase::SLUTSPIL, SeasonPhase::fromRound(11, 'Badmintonligaen', $kind));
        }
    }
}
