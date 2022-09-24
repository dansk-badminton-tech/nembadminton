<?php
declare(strict_types = 1);


namespace App\Console\Commands;

use App\Import\Import;
use App\Jobs\BridgeToHorizon;
use Illuminate\Console\Command;

class TestBridge extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badmintonplayer-api-import:test-bridge {club-id : BadmintonPlayer club id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testing bridge';

    /**
     * Execute the console command.
     *
     *
     * @return int
     */
    public function handle()
    {
        BridgeToHorizon::dispatch($this->argument('club-id'))->onConnection('database');
    }
}
