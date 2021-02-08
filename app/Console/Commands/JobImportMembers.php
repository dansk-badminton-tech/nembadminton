<?php


namespace App\Console\Commands;

use App\Models\User;
use App\Models\Watch;
use FlyCompany\Import\Ranking;
use FlyCompany\Import\Util\Path;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class JobImportMembers extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:job-import-members';

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
        $date = date('dmy');
        $clubs = User::query()->groupBy(['organization_id'])->get(['organization_id'])->toArray();
        $clubIds = Arr::pluck($clubs, 'organization_id');

        $clubIdsChunks = array_chunk($clubIds, 5);

        foreach ($clubIdsChunks as $clubIdsChunk) {
            $this->info('Queuing clubId: ' . implode(',', $clubIdsChunk));
            \App\Jobs\ImportMembers::dispatchNow($date, $clubIdsChunk);
        }

        return 0;
    }
}
