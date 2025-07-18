<?php

namespace App\Console\Commands;

use App\Jobs\LoopsUpdateContact;
use App\Models\User;
use Illuminate\Console\Command;
use Loops\LoopsClient;

class LoopsSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loops:sync {--limit=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync contacts to loops.so';

    /**
     * Execute the console command.
     */
    public function handle(LoopsClient $client)
    {
        /** @var User $user */
        foreach (User::query()->limit($this->option('limit'))->get() as $user) {
            $this->line($user->name.' - user_id:'.$user->id);
            $this->line('Updating contact...');
            LoopsUpdateContact::dispatch($user);
        }
    }
}
