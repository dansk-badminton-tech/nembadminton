<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class OnceSetPrimaryRole extends Command
{
    protected $signature = 'app:once-set-primary-role';

    protected $description = 'Backfill primary_role_id for users who have roles assigned but no primary role set.';

    public function handle()
    {
        $users = User::whereNull('primary_role_id')->get();
        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            setPermissionsTeamId($user->clubhouse_id);
            $roles = $user->roles;

            if ($roles->isEmpty()) {
                $skipped++;
                continue;
            }

            $user->primary_role_id = $roles->first()->id;
            $user->save();
            $updated++;
        }

        $this->info("Updated {$updated} user(s), skipped {$skipped} with no roles.");
    }
}
