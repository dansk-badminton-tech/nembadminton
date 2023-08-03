<?php


namespace FlyCompany\CalendarFeed\Http\Controllers;

use App\Models\Club;
use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class ICalController extends Controller
{

    /**
     * @throws \JsonException
     */
    public function icalClassic(int $clubId, BadmintonPlayer $badmintonPlayerAPI, Request $request) : \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        /** @var Club $club */
        $club = Club::query()->where('id', '=', $clubId)->firstOrFail();

        $calendar = Calendar::create()->name($club->name1);

        $season = BadmintonPlayerHelper::getCurrentSeason();
        $teams = $badmintonPlayerAPI->getClubTeams($season, $clubId);

        if($request->has('only')){
            $onlys = explode(",",$request->input('only'));
            $onlys = array_map('strtolower', $onlys);
            $teams = array_filter($teams, function (Team $team) use ($onlys) {
                return in_array(strtolower($team->name), $onlys,true);
            });
        }

        foreach ($teams as $team){
            if(in_array((int)$team->ageGroupId, [1, 6, 7])){
                $teamFights = $badmintonPlayerAPI->getTeamFights($season, $clubId, $team->ageGroupId, $team->leagueGroupId, $team->name);
                foreach ($teamFights as $teamFight){
                    $event = Event::create()
                                  ->name($this->generateTitle($teamFight["teams"]))
                                  ->description('https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'.$season.',,,,,'.$teamFight["matchId"].',,')
                                  ->startsAt(Carbon::parse($teamFight["gameTime"]));
                    $calendar->event($event);
                }
            }
        }
        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8');

    }

    /**
     * @throws InvalidArgumentException
     */
    public function ical(int $clubId, BadmintonPlayerAPI $badmintonPlayerAPI) : \Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        $matches = $badmintonPlayerAPI->getCurrentLeagueMatches();

        $matches = array_filter($matches, static function (TeamMatch $match) use ($clubId) {
            return $match->clubId1 === $clubId || $match->clubId2 === $clubId;
        });

        // Data clean up
        /** @var TeamMatch[] $matches */
        $matches = (new Collection($matches))->unique('leagueMatchId');

        /** @var Club $club */
        $club = Club::query()->where('id', '=', $clubId)->firstOrFail();

        $calendar = Calendar::create()->name($club->name1);
        foreach ($matches as $match) {
            $event = Event::create()
                          ->name($match->divisionName . ' - '.$match->groupName.': ' . $match->teamName1 . ' vs ' . $match->teamName2)
                          ->addressName($match->venueName)
                            ->description('https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'.$match->seasonId.',,,,,'.$match->leagueMatchId.',,')
                          ->startsAt(Carbon::createFromTimeString($match->matchTime));
            $calendar->event($event);
        }

        return response($calendar->get())
            ->header('Content-Type', 'text/calendar; charset=utf-8');
    }

    public function generateTitle(array $teams1) : string
    {
        $team1Name = $teams1[0] ?? '';
        $team2Name = $teams1[1] ?? '';

        return sprintf("%s VS %s", $team1Name, $team2Name);
    }


}
