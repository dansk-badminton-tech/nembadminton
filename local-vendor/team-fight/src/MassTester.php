<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use App\Models\Club;
use Carbon\Carbon;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\Enricher as ScraperEnricher;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Models\TeamFight;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 *
 */
class MassTester
{
    private BadmintonPlayer $badmintonPlayer;
    private ScraperEnricher $enricher;

    public function __construct(
        BadmintonPlayer $badmintonPlayer,
        ScraperEnricher $enricher
    ){
        $this->badmintonPlayer = $badmintonPlayer;
        $this->enricher = $enricher;
    }

    /**
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     */
    public function checkRound(int $clubId, int $season, int $round)
    {
        /** @var Club $club */
        $club = Club::query()->findOrFail($clubId);

        $teams = $this->badmintonPlayer->getClubTeams($season, $clubId);
        $teams = array_filter($teams, static function (Team $team){
            return $team->isDenmarkSeriesOrAbove();
        });

        $allTeamFights = [];
        foreach ($teams as $team){
            $teamFights = $this->badmintonPlayer->getTeamFights($season, $clubId, (int)$team->ageGroupId, (int)$team->leagueGroupId, $team->name);
            $allTeamFights = [...$allTeamFights, ...$teamFights];
        }

        $teamFightsByRounds = (new Collection($allTeamFights))->groupBy('round');

        $teams = $this->getTeamFights($teamFightsByRounds, $club, $season);
    }

    /**
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     */
    private function getTeamFights(Collection $teamFightsByRounds, Club $club, int $season): array
    {
        $teams = [];
        foreach ($teamFightsByRounds as $roundIndex => $teamFights){
            /** @var TeamFight $teamFight */
            foreach ($teamFights as $teamFight){
                $version = $this->resolveRankingListVersion($teamFight);

                $teamMatch = $this->badmintonPlayer->getTeamMatch((string)$club->id, $teamFight->matchId, (string)$season);
                $guest = $teamMatch->guest;
                if (Str::contains($guest->name, $club->name1)) {
                    $guest->leagueMatchId = $teamFight->matchId;
                    //$guest->squad->league = Helper::convertToLeagueType($badmintonPlayerTeamMatch['league']); // Kind of a hack because we just forward from client. TODO: Make request to badmintonplayer.dk to finde the league based on leagueMatchId
                    $teams[] = $this->enricher->teamMatch($guest, $club->id, $season, $badmintonPlayerTeamMatch['version'] ?? $version);
                } else {
                    $home = $teamMatch->home;
                    $home->leagueMatchId = $teamFight->matchId;
                    //$home->squad->league = Helper::convertToLeagueType($badmintonPlayerTeamMatch['league']); // Kind of a hack because we just forward from client. TODO: Make request to badmintonplayer.dk to finde the league based on leagueMatchId
                    $teams[] = $this->enricher->teamMatch($home, $club->id, $season, $badmintonPlayerTeamMatch['version'] ?? $version);
                }
            }
        }
        return $teams;
    }

    private function resolveRankingListVersion(TeamFight $teamFight) : Carbon
    {

    }

}
