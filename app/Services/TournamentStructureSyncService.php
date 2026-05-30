<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TournamentSyncDryRunRollback;
use App\Models\Season;
use App\Models\TournamentGroup;
use App\Models\TournamentTier;
use App\Util\TournamentStructureMapper;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\TeamMatch;
use Illuminate\Support\Facades\DB;

class TournamentStructureSyncService
{
    public function __construct(private readonly BadmintonPlayerAPI $badmintonPlayerAPI)
    {
    }

    public function sync(bool $dryRun = false): array
    {
        $matches = $this->badmintonPlayerAPI->getCurrentLeagueMatches();
        $stats = $this->emptyStats();

        $stats['matches_processed'] = count($matches);

        if ($dryRun) {
            try {
                DB::transaction(function () use ($matches, &$stats): void {
                    foreach ($matches as $match) {
                        $this->processMatch($match, $stats);
                    }

                    throw new TournamentSyncDryRunRollback();
                });
            } catch (TournamentSyncDryRunRollback) {
                // Dry-run intentionally rolls back all writes.
            }

            return $stats;
        }

        DB::transaction(function () use ($matches, &$stats): void {
            foreach ($matches as $match) {
                $this->processMatch($match, $stats);
            }
        });

        return $stats;
    }

    private function processMatch(TeamMatch $match, array &$stats): void
    {
        $seasonId = $match->seasonId;
        $seasonName = TournamentStructureMapper::seasonNameFromSeasonId($seasonId);

        $this->syncSeason($seasonId, $seasonName, $stats);

        $tierName = TournamentStructureMapper::normalizeDivisionName($match->divisionName);
        $tierId = null;

        if ($tierName !== null) {
            $tierId = $this->syncTier($tierName, $stats);
        } else {
            $stats['groups_with_null_tier']++;
        }

        $groupName = TournamentStructureMapper::normalizeGroupName($match->groupName);
        if ($groupName === null) {
            $stats['groups_skipped_missing_name']++;

            return;
        }

        $phaseType = TournamentStructureMapper::phaseTypeFromGroupName($groupName)->value;
        $this->syncGroup($seasonId, $tierId, $groupName, $phaseType, $stats);
    }

    private function syncSeason(int $seasonId, string $seasonName, array &$stats): void
    {
        $season = Season::query()->find($seasonId);
        if ($season === null) {
            $stats['seasons_created']++;
            Season::query()->create([
                'id' => $seasonId,
                'season_name' => $seasonName,
            ]);

            return;
        }

        if ($season->season_name !== $seasonName) {
            $stats['seasons_updated']++;
            $season->season_name = $seasonName;
            $season->save();

            return;
        }

        $stats['seasons_unchanged']++;
    }

    private function syncTier(string $tierName, array &$stats): ?int
    {
        $tier = TournamentTier::query()->where('tier_name', $tierName)->first();
        if ($tier === null) {
            $stats['tiers_created']++;
            $tier = TournamentTier::query()->create([
                'tier_name' => $tierName,
            ]);

            return $tier?->getAttribute('id');
        }

        $stats['tiers_unchanged']++;

        return $tier->getAttribute('id');
    }

    private function syncGroup(int $seasonId, ?int $tierId, string $groupName, string $phaseType, array &$stats): void
    {
        $query = TournamentGroup::query()
            ->where('season_id', $seasonId)
            ->where('group_name', $groupName);

        if ($tierId === null) {
            $query->whereNull('tier_id');
        } else {
            $query->where('tier_id', $tierId);
        }

        $group = $query->first();

        if ($group === null) {
            $stats['groups_created']++;
            TournamentGroup::query()->create([
                'season_id' => $seasonId,
                'tier_id' => $tierId,
                'group_name' => $groupName,
                'phase_type' => $phaseType,
            ]);

            return;
        }

        if ($group->getRawOriginal('phase_type') !== $phaseType) {
            $stats['groups_updated']++;
            $group->phase_type = $phaseType;
            $group->save();

            return;
        }

        $stats['groups_unchanged']++;
    }

    private function emptyStats(): array
    {
        return [
            'matches_processed' => 0,
            'seasons_created' => 0,
            'seasons_updated' => 0,
            'seasons_unchanged' => 0,
            'tiers_created' => 0,
            'tiers_unchanged' => 0,
            'groups_created' => 0,
            'groups_updated' => 0,
            'groups_unchanged' => 0,
            'groups_with_null_tier' => 0,
            'groups_skipped_missing_name' => 0,
        ];
    }
}
