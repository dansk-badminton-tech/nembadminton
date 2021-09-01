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
        foreach ($badmintonPlayerTeamMatches['leagueMatchIds'] as $badmintonPlayerTeamMatchId) {
            $teamMatch = $this->scraper->getTeamMatch((string)$clubId, (string)$badmintonPlayerTeamMatchId, (string)$season);
            if ($this->isClubTeam($teamMatch->guest->name, $clubId)) {
                $teams[] = $teamMatch->guest;
            } else {
                $teams[] = $teamMatch->home;
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
                            $player = $this->scraper->getPlayerByName($player->name, $version, $season);
                        }catch (MultiplePlayersFoundException $exception){
                            throw new \RuntimeException("Did not find points for player $player->name");
                        }
                    }
                }
                unset($player);
            }
        }

        return $teams;
    }

    private function isClubTeam(string $name, int $clubId) : bool
    {
        /** @var Club $club */
        $club = Club::query()->findOrFail($clubId);

        return Str::contains($name, $club->name1);
    }
}
