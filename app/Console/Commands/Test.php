<?php


namespace App\Console\Commands;

use App\Models\Club;
use App\Models\Member;
use App\Models\Teams;
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
    public function handle(TeamManager $teamManager, SquadManager $squadManager)
    {
        /** @var Teams $sourceTeam */
        $sourceTeam = Teams::query()->findOrFail("px5dVIQu4FaKrw8621E0YLZU");
        $team = $teamManager->copyTeam($sourceTeam);
        foreach ($sourceTeam->squads as $squad){
            $squad = $squadManager->copySquad($squad, $team);
        }
        dump($team);
        return 0;
    }
}
