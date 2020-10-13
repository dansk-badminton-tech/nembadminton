<?php


namespace App\Console\Commands;

use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class JobImportMembers extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:job-import-members
                            {date : ranking to import format \'ddmmyy\'}
                            {--club-ids= : Club Ids}';

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
        $clubIds = explode(',', $this->option('club-ids'));

        if (count($clubIds) === 1 && empty($clubIds[0])) {
            $this->info('Mapping to objects');
            $ranking = Ranking::factory($data);
            $clubIds = [];
            foreach ($ranking->getClubs() as $club) {
                if (is_numeric($club->getId())) {
                    $clubIds[] = $club->getId();
                }
            }
        }

        $clubIdsChunks = array_chunk($clubIds, 5);

        foreach ($clubIdsChunks as $clubIdsChunk) {
            $this->info('Queuing chunk');
            \App\Jobs\ImportMembers::dispatch($date, $clubIdsChunk);
            break;
        }

        return 0;
    }
}
