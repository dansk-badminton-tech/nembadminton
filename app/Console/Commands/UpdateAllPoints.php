<?php
declare(strict_types = 1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class UpdateAllPoints extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:job-import-members {date : ranking to import format \'yyyy-mm-dd\'}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all points in the system';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = $this->argument('date');
        $clubs = User::query()->groupBy(['organization_id'])->get(['organization_id'])->toArray();
        $clubIds = Arr::pluck($clubs, 'organization_id');

        $clubIdsChunks = array_chunk($clubIds, 5);

        foreach ($clubIdsChunks as $clubIdsChunk) {
            $this->info('Queuing clubId: ' . implode(',', $clubIdsChunk));
            \App\Jobs\ImportMembers::dispatch($date, $clubIdsChunk);
        }

        return 0;
    }
}
