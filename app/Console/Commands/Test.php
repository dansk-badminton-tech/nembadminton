<?php


namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use DiDom\Document;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Exception\NoPlayersException;
use FlyCompany\Scraper\Models\Point;
use FlyCompany\Scraper\Parser;
use FlyCompany\TeamFight\Enricher;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\SquadManager;
use FlyCompany\TeamFight\TeamManager;
use FlyCompany\TeamFight\TeamValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
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
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayerAPI $badmintonPlayerAPI, BadmintonPlayer $badmintonPlayer)
    {
        $clubId = 1124;
        $season = 2022;
        $teams = $badmintonPlayer->getClubTeams($season, $clubId);
        foreach ($teams as $team){
            if(in_array((int)$team->ageGroupId, [1, 6, 7])){
                dump($badmintonPlayer->getTeamFights($season, $clubId, $team->ageGroupId, $team->leagueGroupId, $team->name));
            }
        }
        #DB::delete('delete from cache where expiration = ?', []);
//        $playerRanking = $badmintonPlayerAPI->getPlayerRanking(RankingPeriodType::PREVIOUS);
//        foreach ($playerRanking->playerRankings as $index => $playerRanking){
//            dump($playerRanking);
//        }

        return 0;
    }
}
