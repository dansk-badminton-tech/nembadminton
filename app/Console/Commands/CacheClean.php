<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CacheClean extends Command
{
    // The name and signature of the console command
    protected $signature = 'cache:clean';

    // The console command description
    protected $description = 'Clean up expired cache entries';

    // Execute the console command
    public function handle() : int
    {
        $this->info('Cleaning expired cache entries...');

        $affected = DB::table('cache')
                      ->where('expiration', '<=', Carbon::now()->getTimestamp())
                      ->delete();

        $this->info("Deleted $affected expired cache entries.");

        return 0;
    }
}

