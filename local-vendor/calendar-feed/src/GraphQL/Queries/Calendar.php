<?php


namespace FlyCompany\CalendarFeed\GraphQL\Queries;

use App\Models\User;
use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Calendar
{

    public function __construct(private BadmintonPlayer $badmintonPlayer)
    {
    }

    /**
     * @throws \JsonException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {

        /** @var User $user */
        $user = $context->user();
        $clubId = $user->club->badmintonPlayerId;
        $season = BadmintonPlayerHelper::getCurrentSeason();
        $teams = $this->badmintonPlayer->getClubTeams($season, $clubId);
        $events = [];
        foreach ($teams as $team){
            if(in_array((int)$team->ageGroupId, [1, 6, 7])){
                $teamFights = $this->badmintonPlayer->getTeamFights($season, $clubId, $team->ageGroupId, $team->leagueGroupId, $team->name);
                foreach ($teamFights as $teamFight){
                    $start = Carbon::parse($teamFight["gameTime"]);
                    $format = "Y-m-d H:i:s";
                    $startFormat = $start->format($format);
                    $endFormat = $start->addHours(3)->format($format);
                    $events[] = [
                        'title' => $this->generateTitle($teamFight["teams"]),
                        'start' => $startFormat,
                        'end' => $endFormat,
                        'content' => 'Klik for info',
                        'matchId' => $teamFight['matchId'],
                        'contentFull' => '<a target="_blank" href="https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,'.$season.',,,,,'.$teamFight["matchId"].',,">Link til kampen på badmintonplayer.dk</a>'
                    ];
                }
            }
        }
        return $events;
    }

    /**
     * @param array $teams1
     *
     * @return string
     */
    public function generateTitle(array $teams1) : string
    {
        $team1Name = $teams1[0] ?? '';
        $team2Name = $teams1[1] ?? '';

        return sprintf("%s VS %s", $team1Name, $team2Name);
    }

}
