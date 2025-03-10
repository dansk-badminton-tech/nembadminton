<?php

namespace App\Console\Commands;

use App\Models\CancellationCollector;
use App\Models\User;
use Illuminate\Console\Command;

class OnceConvertCancellationCollectorToClubhouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:once-convert-cancellation-collector-to-clubhouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //User::query()->where('email', 'danielflynygaard@gmail.com')->first()->assignRole('super-admin');

        foreach (CancellationCollector::all() as $cancellationCollector) {
            $cancellationCollector->clubhouse()->associate($cancellationCollector->user->clubhouse);
            $cancellationCollector->save();
        }
    }
}
