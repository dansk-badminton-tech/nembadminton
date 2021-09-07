<?php


namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use DiDom\Document;
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
use Illuminate\Console\Command;
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
     * @param BadmintonPlayer $scraper
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper)
    {

        $html = file_get_contents(__DIR__.'/../../../test.html');
        $document = new Document($html);
        $trs = $document->find('table.RankingListGrid tr');

        // Remove top of table
        array_shift($trs);

        $testRow = $trs[0];
        dump($testRow->has('td.name'),$testRow->has('td.playerid'));
        //        $clubId = 1622;
//        $player = $scraper->getPlayerByName("Peter Lose Iversen", Carbon::create(2020, 8, 1), "2020");

        //$rankingLists = $scraper->getAllRankingListPlayers(2020, (string)$clubId, Carbon::create(2020, 8, 1));
        //$playersWithPoints = BadmintonPlayerHelper::collapseRankingLists($rankingLists);

        //dump($playersWithPoints);
//        dump($player);

        return 0;
    }
}
