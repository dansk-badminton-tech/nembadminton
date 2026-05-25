<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CanonicalTournamentTier;
use App\Models\TournamentTier;
use Illuminate\Database\Seeder;

class TournamentTierSeeder extends Seeder
{
    /**
     * Seed the tournament_tiers table from the canonical enum so that the
     * autocomplete / sync process always has a known baseline of tier names.
     *
     * Idempotent: upserts by tier_name (which is unique).
     */
    public function run(): void
    {
        foreach (CanonicalTournamentTier::cases() as $case) {
            TournamentTier::query()->updateOrCreate(
                ['tier_name' => $case->value],
                [],
            );
        }
    }
}
