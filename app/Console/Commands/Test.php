<?php


namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use FlyCompany\Members\PointsManager;
use FlyCompany\Scraper\BadmintonPlayer;
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
     * @param PointsManager $pointsManager
     * @return int
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, PointsManager $pointsManager)
    {
        $clubId = 1622;
        $teams = $scraper->searchPlayer($clubId, '900910-17');

        foreach ($teams as $team) {
            $scraper->getTeamFights($season, $clubId, $team['ageGroupId'], $team['leagueGroupID'], $team['name']);
        }

        return 0;
    }
}
