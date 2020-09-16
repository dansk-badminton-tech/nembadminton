<?php
declare(strict_types = 1);


namespace App\Console\Commands;

use App\Models\Point;
use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:points {data : ranking to import format \'ddmmyy\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import rank points and attach them to players';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->argument('date');
        $path = Path::getRankingPath($date);
        $this->info('Loading ' . $path);
        $data = XMLHelper::loadXML($path, Storage::disk());
        $this->info('Mapping to objects');
        $ranking = Ranking::factory($data);

        foreach ($ranking->getClubs() as $club) {
            foreach ($club->getMembers() as $member) {
                $memberModel = \App\Models\Member::query()->where('refId', $member->getId())->first();
                if($memberModel !== null){
                    $this->info('Adding points to '.$member->getName());
                    foreach ($member->getMemberVintages() as $memberVintage){
                        foreach ($memberVintage->getPoints() as $point){
                            Point::query()->create([
                                'points' => $point->getPoints(),
                                'position' => $point->getPosition(),
                                'category' => $point->getCategory(),
                                'cll' => $point->getCll(),
                                'clh' => $point->getClh(),
                                'vintage' => $memberVintage->getName(),
                                'member_id' => $memberModel->id
                            ]);
                        }
                    }
                }
            }
        }
        return 0;
    }
}
