<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use App\Import\Import;
use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportClubs extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:clubs {date : ranking to import format \'ddmmyy\'} {--club-ids= : Club Ids} {--import-members : queue import members job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update clubs';

    /**
     * Execute the console command.
     *
     * @param Import $import
     *
     * @return int
     */
    public function handle(Import $import)
    {
        $date = $this->argument('date');
        $clubIdsString = $this->option('club-ids');
        if($clubIdsString !== null){
            $clubIds = explode(',', $clubIdsString);
        }else{
            $clubIds = [];
        }
        $import->importClubs($date, $clubIds);

        if ($this->option('import-members')) {
            \App\Jobs\ImportMembers::dispatch($date, $clubIds);
        }

        return 0;
    }
}
