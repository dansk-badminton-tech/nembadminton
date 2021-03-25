<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use App\Import\Import;
use Illuminate\Console\Command;

/**
 * Class ImportMembers
 *
 * @package App\Console\Commands
 */
class ImportMembers extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xml-import:members
                            {date : ranking to import format \'yyyy-mm-dd\'}
                            {--club-ids= : Club Ids}
                            {--skip-points : Skip points import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import members to club from xml';

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
        $clubIds = explode(',', $this->option('club-ids'));

        $import->importMembers($date, $clubIds);

        return 0;
    }
}
