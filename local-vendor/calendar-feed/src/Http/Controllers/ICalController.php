<?php


namespace FlyCompany\CalendarFeed\Http\Controllers;

use App\Models\Club;
use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class ICalController extends Controller
{

    public function ical(int $clubId, BadmintonPlayerAPI $badmintonPlayerAPI)
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

}
