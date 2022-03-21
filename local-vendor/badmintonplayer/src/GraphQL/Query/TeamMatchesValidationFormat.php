<?php

declare(strict_types=1);

namespace FlyCompany\BadmintonPlayer\GraphQL\Query;

use App\Models\Member;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatchLineup;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\TeamFight\LeagueType;
use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Point;
use FlyCompany\TeamFight\Models\Squad;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use JsonException;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class TeamMatchesValidationFormat
{
    private BadmintonPlayerAPI $badmintonPlayerAPI;

    public function __construct(BadmintonPlayerAPI $badmintonPlayerAPI)
    {
        $this->badmintonPlayerAPI = $badmintonPlayerAPI;
    }

    /**
     * Return a value for the field.
     *
     * @param  @param  null  $root Always null, since this field has no parent.
     * @param array<string, mixed> $args The field arguments passed by the client.
     * @param GraphQLContext $context Shared between all fields.
     * @param ResolveInfo $resolveInfo Metadata for advanced query resolution.
     * @return array
     * @throws JsonException
     * @throws InvalidArgumentException
     * @throws ExceptionInterface
     */
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $matchIds = $args['matchIds'];
        $matches = $this->badmintonPlayerAPI->getPlayedLeagueMatches()->getByMatchIds($matchIds);
        $version = $args['version'];
        $players = $this->badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::fromDate($version));
        $clubId = $args['clubId'];
        $playersInClub = $players->getPlayerRankingCollection()->getByClubId($clubId);

        $matchesData = [];
        foreach ($matchIds as $matchId) {
            $match = $matches->firstOrFail(static function(TeamMatchLineup $matchLineup) use ($matchId) {
                return $matchLineup->match->leagueMatchId === $matchId;
            });
            $matchData = [
                'name' => $match->match->divisionName,
                'leagueMatchId' => (string)$match->match->leagueMatchId
            ];
            $squad = new Squad();
            $squad->playerLimit = 10; // Calculate based on team size
            $squad->league = LeagueType::OTHER->value; // Make this calculation based on divisionName
            foreach ($match->combinedTeamMatches as $category) {
                $categoryModel = new Category();
                $categoryModel->name = $category->teamPlayers[0]->getShortDiscipline();
                $categoryModel->category = $category->teamPlayers[0]->getDiscipline()->shortName();
                /** @var \FlyCompany\BadmintonPlayerAPI\Models\Player[] $players */
                $matchPlayerMetas = $category->getPlayersByClubId($clubId);
                $players = Arr::pluck($matchPlayerMetas, 'player');
                foreach ($players as $player) {
                    $player = $playersInClub->getByPlayerNumber($player->playerNumber);

                    $playerModel = new Player();
                    $playerModel->name = $player->name;
                    $playerModel->gender = $player->gender;
                    $playerModel->refId = $player->playerNumber;
                    $playerModel->points = Util::convertToPointsList($player, $version);
                    $categoryModel->players[] = $playerModel;
                }
                $squad->categories[] = $categoryModel;
            }
            $matchData['squad'] = $squad;
            $matchesData[] = $matchData;
        }
        return $matchesData;
    }

}
