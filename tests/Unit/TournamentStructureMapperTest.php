<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\TournamentPhaseType;
use App\Util\TournamentStructureMapper;
use PHPUnit\Framework\TestCase;

class TournamentStructureMapperTest extends TestCase
{
    /**
     * @test
     */
    public function it_builds_season_name_from_season_id(): void
    {
        $this->assertSame('2025/2026', TournamentStructureMapper::seasonNameFromSeasonId(2025));
    }

    /**
     * @test
     */
    public function it_normalizes_division_name_and_strips_trailing_parentheses(): void
    {
        $this->assertSame('1. division', TournamentStructureMapper::normalizeDivisionName("\t1. division (grundspil)\t"));
        $this->assertSame('Badmintonligaen', TournamentStructureMapper::normalizeDivisionName(" Badmintonligaen (oversidder-runder) "));
    }

    /**
     * @test
     */
    public function it_returns_null_for_empty_division_name(): void
    {
        $this->assertNull(TournamentStructureMapper::normalizeDivisionName(null));
        $this->assertNull(TournamentStructureMapper::normalizeDivisionName(" \t "));
    }

    /**
     * @test
     */
    public function it_resolves_regular_season_groups(): void
    {
        $this->assertSame(TournamentPhaseType::REGULAR_SEASON, TournamentStructureMapper::phaseTypeFromGroupName('Grundspil'));
        $this->assertSame(TournamentPhaseType::REGULAR_SEASON, TournamentStructureMapper::phaseTypeFromGroupName('Pulje 3'));
    }

    /**
     * @test
     */
    public function it_resolves_promotion_and_relegation_groups(): void
    {
        $this->assertSame(TournamentPhaseType::PROMOTION_RELEGATION, TournamentStructureMapper::phaseTypeFromGroupName('Kvalifikation til 2.division pulje A'));
        $this->assertSame(TournamentPhaseType::PROMOTION_RELEGATION, TournamentStructureMapper::phaseTypeFromGroupName('Nedrykning fra 3. division pulje B'));
    }

    /**
     * @test
     */
    public function it_resolves_playoff_groups(): void
    {
        $this->assertSame(TournamentPhaseType::PLAYOFF, TournamentStructureMapper::phaseTypeFromGroupName('Kvartfinaler'));
        $this->assertSame(TournamentPhaseType::PLAYOFF, TournamentStructureMapper::phaseTypeFromGroupName('Bronzekamp'));
    }

    /**
     * @test
     */
    public function it_resolves_byes_and_paper_teams(): void
    {
        $this->assertSame(TournamentPhaseType::BYES_PAPER_TEAM, TournamentStructureMapper::phaseTypeFromGroupName('Oversidder-runder/papirhold'));
        $this->assertSame(TournamentPhaseType::BYES_PAPER_TEAM, TournamentStructureMapper::phaseTypeFromGroupName('3. division (oversidder-runde)'));
    }

    /**
     * @test
     */
    public function it_falls_back_to_other_when_unknown_or_missing(): void
    {
        $this->assertSame(TournamentPhaseType::OTHER, TournamentStructureMapper::phaseTypeFromGroupName('Ukendt fase'));
        $this->assertSame(TournamentPhaseType::OTHER, TournamentStructureMapper::phaseTypeFromGroupName(null));
    }
}
