<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TournamentStructureSyncService;
use Illuminate\Console\Command;

class TournamentStructureSync extends Command
{
    protected $signature = 'tournament:sync-structure {--dry-run : Parse and compare without writing to DB}';

    protected $description = 'Sync tournament seasons, tiers and groups from badmintonplayer.dk';

    public function handle(TournamentStructureSyncService $service): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = $service->sync($dryRun);

        $this->line($dryRun ? 'Dry-run completed.' : 'Sync completed.');
        $this->line('Matches processed: ' . $stats['matches_processed']);
        $this->line('Seasons - created: ' . $stats['seasons_created'] . ', updated: ' . $stats['seasons_updated'] . ', unchanged: ' . $stats['seasons_unchanged']);
        $this->line('Tiers - created: ' . $stats['tiers_created'] . ', updated: ' . $stats['tiers_updated'] . ', unchanged: ' . $stats['tiers_unchanged'] . ', unknown rank: ' . $stats['tier_rank_unknown']);
        $this->line('Groups - created: ' . $stats['groups_created'] . ', updated: ' . $stats['groups_updated'] . ', unchanged: ' . $stats['groups_unchanged']);
        $this->line('Groups with null tier: ' . $stats['groups_with_null_tier'] . ', skipped missing name: ' . $stats['groups_skipped_missing_name']);

        return self::SUCCESS;
    }
}
