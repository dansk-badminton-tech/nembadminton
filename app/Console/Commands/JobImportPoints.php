<?php


namespace App\Console\Commands;

use App\Models\Point;
use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class JobImportPoints extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:job-import-points
                            {date : ranking to import format \'ddmmyy\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $memberIds = [];
        foreach ($ranking->getClubs() as $club) {
            foreach ($club->getMembers() as $member) {
                $memberIds[] = $member->getId();
            }
        }

        $memberIdsChunks = array_chunk($memberIds, 500);

        foreach ($memberIdsChunks as $memberIdsChunk) {
            $this->info('Queuing chunk');
            \App\Jobs\ImportPoints::dispatch($memberIdsChunk);
        }

        return 0;
    }
}
