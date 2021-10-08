<?php

declare(strict_types=1);


namespace FlyCompany\Scraper\GraphQL\Mutations;

use App\Models\Club;
use Carbon\Carbon;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Enricher;
use FlyCompany\Scraper\Exception\MultiplePlayersFoundException;
use FlyCompany\Scraper\Exception\NoPlayersFoundInTeamMatchException;
use FlyCompany\Scraper\Helper;
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
     * @param  array<string, mixed>  $args  The field arguments passed by the client.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context  Shared between all fields.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo  Metadata for advanced query resolution.
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
                $guest->league = Helper::convertToLeagueType($badmintonPlayerTeamMatch['league']); // Kind of a hack because we just forward from client. TODO: Make request to badmintonplayer.dk to finde the league based on leagueMatchId
                $teams[] = $guest;
            } else {
                $home = $teamMatch->home;
                $home->leagueMatchId = $leagueMatchId;
                $home->league = Helper::convertToLeagueType($badmintonPlayerTeamMatch['league']); // Kind of a hack because we just forward from client. TODO: Make request to badmintonplayer.dk to finde the league based on leagueMatchId
                $teams[] = $home;
            }
        }

        $playersWithPoints = [];
        foreach ($this->resolveClubs($clubId) as $clubId) {
            $rankingLists = $this->scraper->getAllRankingListPlayers($season, (string)$clubId, $version);
            $playersWithPoints += BadmintonPlayerHelper::collapseRankingLists($rankingLists);
        }

        // Enrich players with points
        foreach ($teams as $team) {
            foreach ($team->squad->categories as $category) {
                foreach ($category->players as &$player) {
                    if (isset($playersWithPoints[$player->name])) {
                        $player = $playersWithPoints[$player->name];
                        foreach ($player->points as $point) {
                            $point->version = $version;
                        }
                    } else {
                        try {
                            if (!$player->isNoBody()) {
                                $player = $this->scraper->getPlayerByBadmintonPlayerId(
                                    $player->badmintonPlayerId,
                                    $version,
                                    $season
                                );
                            } else {
                                $player = null;
                            }
                        } catch (MultiplePlayersFoundException $exception) {
                            throw new \RuntimeException("Multiple players named $player->name");
                        }
                    }
                }
                unset($player);
                $category->players = array_filter($category->players, static function ($player) {
                    return $player !== null;
                });
            }
        }

        return $teams;
    }

    private function resolveClubs(int $clubId) : array
    {
        $clubIds = [$clubId];
        if ($clubId < 0) {
            switch ($clubId) {
                case -473: // RSL ODENSE OBK
                    $clubIds = [1392];
                    break;
                case -2: // Team Skælskør-Slagelse
                    $clubIds = [327, 1157];
                    break;
                case -439: // Højbjerg/Via Biler
                    $clubIds = [25];
                    break;
                case -3: // Vendsyssel
                    $clubIds = [1494, 1500, 1497];
                    break;
            }
        }

        return $clubIds;
    }
}
