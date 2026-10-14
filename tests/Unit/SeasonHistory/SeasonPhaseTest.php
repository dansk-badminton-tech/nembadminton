<?php

declare(strict_types=1);

namespace Tests\Unit\SeasonHistory;

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
            'round 12 in Badmintonligaen is kvalifikation' => [12, 'Badmintonligaen', SeasonPhase::KVALIFIKATION],
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
}
