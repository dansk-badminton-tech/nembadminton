<?php


namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use FlyCompany\Scraper\BadmintonPlayer;
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
     * @param Enricher        $enricher
     * @param TeamManager     $teamManager
     * @param SquadManager    $squadManager
     *
     * @return int
     * @throws \DiDom\Exceptions\InvalidSelectorException
     * @throws \JsonException
     */
    public function handle(BadmintonPlayer $scraper, Enricher $enricher, TeamManager $teamManager, SquadManager $squadManager)
    {
        $teamMatch = $scraper->getTeamMatch('1622', '386334', '2020');
        $version = '2021-02-17';
        foreach ($teamMatch->home->squad->categories as $category) {
            $enricher->players($category->players, $version);
        }

        dd($teamMatch);

        return 0;
    }
}
