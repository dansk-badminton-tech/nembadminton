<?php
declare(strict_types = 1);


namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class ImportPoints implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $membersIds;

    /**
     * Create a new job instance.
     *
     * @param array $memberIds
     */
    public function __construct(array $memberIds)
    {
        $this->membersIds = $memberIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Artisan::call('import:points', ['date' => '300820', '--member-ids' => implode(',', $this->membersIds)]);
    }
}
