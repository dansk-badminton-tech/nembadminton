<?php

declare(strict_types=1);

namespace FlyCompany\TeamFight;

use App\Models\Club;
use Carbon\Carbon;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
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
        ScraperEnricher $enricher,
    ){
        $this->badmintonPlayer = $badmintonPlayer;
        $this->enricher = $enricher;
    }

    /**
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     * @return Team[]
     */
    public function checkRound(int $clubId, int $season, int $round) : array
    {
        /** @var Club $club */
        $club = Club::query()->findOrFail($clubId);

        $teams = $this->badmintonPlayer->getClubTeams($season, $clubId);
        $teams = array_filter($teams, static function (Team $team){
            return $team->isRegionSeriesOrAbove();
        });

        $allTeamFights = [];
        foreach ($teams as $team){
            $teamFights = $this->badmintonPlayer->getTeamFights($season, $clubId, (int)$team->ageGroupId, (int)$team->leagueGroupId, $team->name);
            $allTeamFights = [...$allTeamFights, ...$teamFights];
        }

        $teamFightsByRounds = (new Collection($allTeamFights))->groupBy('round');
        $teamFightsByRounds = new Collection([$teamFightsByRounds->get($round)]);

        return $this->getTeamFights($teamFightsByRounds, $club, $season);
    }

    /**
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \JsonException
     * @return Team[]
     */
    private function getTeamFights(Collection $teamFightsByRounds, Club $club, int $season): array
    {
        $teams = [];
        foreach ($teamFightsByRounds as $roundIndex => $teamFights){
            /** @var TeamFight $teamFight */
            foreach ($teamFights as $teamFight){
                $version = $this->resolveRankingListVersion($teamFight, $season);

                $teamMatch = $this->badmintonPlayer->getTeamMatch((string)$club->id, $teamFight->matchId, (string)$season);
                $guest = $teamMatch->guest;
                if (Str::contains($guest->name, $club->name1)) {
                    $guest->teamFight = $teamFight;
                    $guest->leagueMatchId = $teamFight->matchId;
                    $guest->squad->league = $guest->getLeagueType()->value;
                    $teams[] = $this->enricher->teamMatch($guest, $club->id, $season, $version);
                } else {
                    $home = $teamMatch->home;
                    $home->teamFight = $teamFight;
                    $home->leagueMatchId = $teamFight->matchId;
                    $home->squad->league = $home->getLeagueType()->value;
                    $teams[] = $this->enricher->teamMatch($home, $club->id, $season, $version);
                }
            }
        }
        return $teams;
    }

    public function resolveRankingListVersion(TeamFight $teamFight, int $season) : Carbon
    {
        $rankingMonths = BadmintonPlayerHelper::filterToRankingMonths($this->badmintonPlayer->getVersions($season));
        return $rankingMonths->first(static function(Carbon $rankingMonth) use ($teamFight){
            $gameTime = $teamFight->getGameTime();
            if($gameTime->month === $rankingMonth->month && $gameTime->day > 9){
                return true;
            }
            if($gameTime->month-1 === $rankingMonth->month && $gameTime->day < 10){
                return true;
            }
            return false;
        });
    }

}
