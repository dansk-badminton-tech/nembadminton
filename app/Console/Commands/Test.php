<?php


namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Release;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use FlyCompany\TeamFight\TeamValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     *
     * @return int
     * @throws \JsonException
     */
    public function handle(TeamValidator $teamValidator)
    {
        /** @var User $user */
        $user = User::query()->find(1);
        $user->notifyNow(new Release('Ny feature', 'Du kan nu rediger'));
        $user = User::query()->find(2);
        $user->notifyNow(new Release('Stop', 'Du kan nu rediger'));
    }
}
