<?php

namespace App\Jobs;

use App\Models\Club;
use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class BridgeToHorizon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $clubId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $clubId)
    {
        $this->clubId = $clubId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $clubId = $this->clubId;
        Bus::chain([
            new ImportMembers([$this->clubId]),
            new ImportPoints($this->clubId, RankingPeriodType::CURRENT),
            function() use ($clubId) {
                Club::query()->where('id', '=', $clubId)->update(['initialized' => true]);
            }
        ])->onConnection('redis')->dispatch();
    }
}
