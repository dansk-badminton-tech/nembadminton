<?php
declare(strict_types = 1);


namespace FlyCompany\Scraper\GraphQL\Mutations;

use App\Models\Club;
use Carbon\Carbon;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Enricher;
use FlyCompany\Scraper\Exception\MultiplePlayersFoundException;
use FlyCompany\Scraper\Exception\NoPlayersFoundInTeamMatchException;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\TeamFight\SquadManager;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Str;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class BadmintonPlayerTeamMatchsImport
 *
 * @package FlyCompany\Scraper\GraphQL\Mutations
 */
class BadmintonPlayerTeamMatchesImport
{

    /**
     * @var BadmintonPlayer
     */
    private BadmintonPlayer $scraper;

    /**
     * @var Enricher
     */
    private Enricher $enricher;

    /**
     * @var SquadManager
     */
    private SquadManager $squadManager;

    public function __construct(BadmintonPlayer $scraper, Enricher $enricher, SquadManager $squadManager)
    {
        $this->scraper = $scraper;
        $this->enricher = $enricher;
        $this->squadManager = $squadManager;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed>                                $args        The field arguments passed by the client.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context     Shared between all fields.
     * @param \GraphQL\Type\Definition\ResolveInfo                $resolveInfo Metadata for advanced query resolution.
     *
     * @return mixed
     * @throws \JsonException
     * @throws \Throwable
     *
     * @throws \DiDom\Exceptions\InvalidSelectorException
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $badmintonPlayerTeamMatches = $args['input'];
        $season = $badmintonPlayerTeamMatches['season'];
        $clubId = $badmintonPlayerTeamMatches['clubId'];
        $version = $badmintonPlayerTeamMatches['version'];
        /** @var Team[] $teams */
        $teams = [];
        foreach ($badmintonPlayerTeamMatches['leagueMatches'] as $badmintonPlayerTeamMatch) {
            $leagueMatchId = (string)$badmintonPlayerTeamMatch['id'];
            $teamMatch = $this->scraper->getTeamMatch((string)$clubId, $leagueMatchId, (string)$season);
            $guest = $teamMatch->guest;
            if (Str::contains($guest->name, $badmintonPlayerTeamMatch['teamNameHint'])) {
                $guest->leagueMatchId = $leagueMatchId;
                $teams[] = $guest;
            } else {
                $home = $teamMatch->home;
                $home->leagueMatchId = $leagueMatchId;
                $teams[] = $home;
            }
        }


        $rankingLists = $this->scraper->getAllRankingListPlayers($season, (string)$clubId, $version);
        $playersWithPoints = BadmintonPlayerHelper::collapseRankingLists($rankingLists);

        // Enrich players with points
        foreach ($teams as $team) {
            foreach ($team->squad->categories as $category) {
                foreach ($category->players as &$player) {
                    if (isset($playersWithPoints[$player->name])) {
                        $player = $playersWithPoints[$player->name];
                        foreach ($player->points as $point) {
                            $point->version = $version;
                        }
                    }else{
                        try{
                            if(!$player->isNoBody()){
                                $player = $this->scraper->getPlayerByBadmintonPlayerId($player->badmintonPlayerId, $version, $season);
                            }
                        }catch (MultiplePlayersFoundException $exception){
                            throw new \RuntimeException("Multiple players named $player->name");
                        }
                    }
                }
                unset($player);
            }
        }

        return $teams;
    }
}
