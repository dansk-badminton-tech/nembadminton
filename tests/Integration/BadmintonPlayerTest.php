<?php

declare(strict_types=1);

namespace Tests\Integration;

use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\Parser;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Cache;
use Tests\CreatesApplication;

class BadmintonPlayerTest extends TestCase
{

    use CreatesApplication;

    private BadmintonPlayer $badmintonPlayer;

    public function getBadmintonPlayer(): BadmintonPlayer
    {
        return new BadmintonPlayer(new Parser(), Cache::store());
    }

    /**
     * @test
     * @return void
     */
    public function getLeagueScoreboardName(): void
    {
        $clubId = 1622; // Skovbakken
        $leagueGroupId = 13768;
        $ageGroupID = 1;
        $season = 2021;
        $leagueScoreboard = $this->getBadmintonPlayer()->getLeagueScoreboard($season, $clubId, $ageGroupID, $leagueGroupId);

        $this->assertSame('3. division Pulje 1', $leagueScoreboard->getLeagueName());
    }

    /**
     * @test
     * @return void
     * @throws \JsonException
     */
    public function getLeagueScoreboardEntriesTeamNames(): void
    {
        $clubId = 1622; // Skovbakken
        $leagueGroupId = 13768;
        $ageGroupID = 1;
        $season = 2021;
        $leagueScoreboard = $this->getBadmintonPlayer()->getLeagueScoreboard($season, $clubId, $ageGroupID, $leagueGroupId);

        $clubNames = [
            "Viby J 2",
            "abc Aalborg 2",
            "Skovbakken (N)",
            "Ikast 3 (O)",
            "Højbjerg 4 (O)",
            "Team Favrskov (O)",
            "Vendsyssel 2",
            "Gug"
        ];

        foreach ($leagueScoreboard->getEntries() as $entry){
            $this->assertContains($entry->getTeam()->getName(), $clubNames);
        }
    }

    /**
     * @test
     * @return void
     * @throws \JsonException
     */
    public function getLeagueScoreboardEntriesTeamIds(): void
    {
        $clubId = 1622; // Skovbakken
        $leagueGroupId = 13768;
        $ageGroupID = 1;
        $season = 2021;
        $leagueScoreboard = $this->getBadmintonPlayer()->getLeagueScoreboard($season, $clubId, $ageGroupID, $leagueGroupId);

        $leagueGroupTeamIds = [
            92128,
            92129,
            92130,
            92131,
            92132,
            92133,
            92134,
            92135
        ];

        foreach ($leagueScoreboard->getEntries() as $entry){
            $this->assertSame(13768, $entry->getTeam()->getLeagueGroupId());
            $this->assertSame(1, $entry->getTeam()->getAgeGroupId());
            $this->assertContains($entry->getTeam()->getLeagueGroupTeamId(), $leagueGroupTeamIds);
        }
    }

    /**
     * @test
     * @return void
     * @throws \JsonException
     */
    public function getLeagueScoreboardEntries(): void
    {
        $clubId = 1622; // Skovbakken
        $leagueGroupId = 13768;
        $ageGroupID = 1;
        $season = 2021;
        $leagueScoreboard = $this->getBadmintonPlayer()->getLeagueScoreboard($season, $clubId, $ageGroupID, $leagueGroupId);

        foreach ($leagueScoreboard->getEntries() as $entry){
            $this->assertTrue($entry->getNumberOfFights() > 0);
            $this->assertTrue($entry->getNumberOfFightWins() > 0);
            $this->assertTrue($entry->getNumberOfWinSets() > 0);
            $this->assertTrue($entry->getNumberOfLostSets() > 0);
            $this->assertTrue($entry->getTotalPoints() > 0);
        }
    }

}
