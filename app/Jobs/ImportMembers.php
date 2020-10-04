<?php
declare(strict_types = 1);


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class ImportMembers implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $clubIds;

    /**
     * Create a new job instance.
     *
     * @param array $clubIds
     */
    public function __construct(array $clubIds)
    {
        $this->clubIds = $clubIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('import:members', ['date' => '300820', '--club-ids' => implode(',', $this->clubIds)]);
    }
}
