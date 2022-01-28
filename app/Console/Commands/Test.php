<?php


namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use DiDom\Document;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Models\Player;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Models\Team;
use FlyCompany\Scraper\Parser;
use FlyCompany\TeamFight\Enricher;
use FlyCompany\TeamFight\GraphQL\Mutations\ValidateHelper;
use FlyCompany\TeamFight\MassTester;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\SquadManager;
use FlyCompany\TeamFight\TeamManager;
use FlyCompany\TeamFight\TeamValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name {club-id} {round}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(MassTester $massTester, TeamValidator $teamValidator)
    {
        $season = 2021;
        $teams = $massTester->checkRound((int)$this->argument('club-id'), $season, (int)$this->argument('round'));
        $serializer = SerializerHelper::getSerializer();

        $teamsArray = json_decode(\json_encode($teams, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        $crossTeamConflicts = $this->crossSquadCheck($teamsArray, $serializer, $teamValidator);
        $squadConflicts = $this->squadConflicts($teamsArray, $serializer, $teamValidator);

        $rowData = array_map(static function(Team $team) use ($season, $massTester) {
            return [$team->name, $team->league, $massTester->resolveRankingListVersion($team->teamFight, $season), $team->teamFight->getGameTime(), $team->teamFight->round];
        }, $teams);
        $this->table(['name', 'league', 'version', 'gametime', 'round'], $rowData);
        //foreach ($teams as $team) {
            //$this->line("$team->name version: {$massTester->resolveRankingListVersion($team->teamFight, $season)} gametime: {$team->teamFight->getGameTime()} round: {$team->teamFight->round}");
            //$this->printTeams($team);
        //}
        $this->line('Cross Team Conflict');
        $this->printConflicts($crossTeamConflicts);
        $this->line('Team Conflict');
        $this->printConflicts($squadConflicts);
        return 0;
    }

    /**
     * @param  mixed  $teams
     * @param  Serializer  $serializer
     * @param  TeamValidator  $teamValidator
     * @return Collection
     * @throws ExceptionInterface
     */
    private function crossSquadCheck(mixed $teams, Serializer $serializer, TeamValidator $teamValidator): Collection
    {
        [$leagueAndBelowTeamPair, $firstDivisionAndBelowTeamPair, $rest] = ValidateHelper::splitTeams(
            new Collection($teams)
        );

        $squads = $leagueAndBelowTeamPair->pluck('squad');
        $squads = $serializer->denormalize($squads->toArray(), Squad::class.'[]');
        $playingToHighLeague = $teamValidator->validateCrossSquadsLeague($squads);

        $squads = $firstDivisionAndBelowTeamPair->pluck('squad');
        $squads = $serializer->denormalize($squads->toArray(), Squad::class.'[]');
        $playingToHighFirstDivision = $teamValidator->validateCrossSquadsLeague($squads);

        $squads = $rest->pluck('squad');
        $squads = $serializer->denormalize($squads->toArray(), Squad::class.'[]');
        $playingToHighBelowFirstDiv = $teamValidator->validateCrossSquads($squads);

        return $playingToHighLeague->merge($playingToHighFirstDivision)->merge($playingToHighBelowFirstDiv);
    }

    /**
     * @param  Team  $team
     * @return void
     */
    private function printTeams(Team $team): void
    {
        $categories = [];
        foreach ($team->squad->categories as $category) {
            $categories[] = [
                $category->category,
                array_reduce($category->players, static function (string $carry, Player $player) {
                    return $carry.$player->name.PHP_EOL;
                }, ''),
                array_reduce($category->players, static function (string $carry, Player $player) use ($category) {
                    return $carry.$player->getPlayerCategoryPoint($category->category).PHP_EOL;
                }, ''),
                array_reduce($category->players, static function (string $carry, Player $player) use ($category) {
                    return $carry.$player->getLevelPoints().PHP_EOL;
                }, '')
            ];
        }
        $this->table(['kategori', 'navn', 'category points', 'level points'], $categories);
    }

    /**
     * @param  mixed  $teamsArray
     * @param  Serializer  $serializer
     * @param  TeamValidator  $teamValidator
     * @return Collection
     * @throws ExceptionInterface
     */
    private function squadConflicts(mixed $teamsArray, Serializer $serializer, TeamValidator $teamValidator): Collection
    {
        $squads = new Collection($teamsArray);
        $squads = $squads->pluck('squad');
        $squads = $serializer->denormalize($squads->toArray(), Squad::class.'[]');
        $squadConflicts = [];
        foreach ($squads as $squad) {
            $squadConflicts = array_merge($teamValidator->validateSquad($squad), $squadConflicts);
        }
        return new Collection($squadConflicts);
    }

    /**
     * @param  Collection  $conflicts
     * @return void
     */
    private function printConflicts(Collection $conflicts): void
    {
        foreach ($conflicts as $conflict) {
            $hasYouthPlayerPartner = $conflict['hasYouthPlayerPartner'] ?? false;
            if(!$conflict['isYouthPlayer'] && !$hasYouthPlayerPartner){
                $nameAndCategory = implode(
                    ', ',
                    array_map(static function (array $player) {
                        return $player['name'].' '.$player['category'];
                    }, $conflict['belowPlayer'])
                );
                $this->line($conflict['name'].' - '.$nameAndCategory);
            }
        }
    }
}
