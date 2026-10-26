<?php

declare(strict_types=1);

namespace App\SeasonHistory;

use App\Enums\SeasonPhase;
use App\Models\Clubhouse;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\TeamRound;
use App\Models\TeamRoundScenario;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Loads a Clubhouse's Season History in a fixed number of queries, however many TeamRounds and players it has.
 */
class SeasonHistoryLoader
{
    public function load(Clubhouse $clubhouse, int $seasonStartYear): SeasonHistory
    {
        $teamRounds = TeamRound::query()
            ->where('clubhouse_id', $clubhouse->id)
            ->where(fn (Builder $query) => $query->where('season_id', $seasonStartYear)->orWhereNull('season_id'))
            ->get()
            ->filter(fn (TeamRound $teamRound) => $teamRound->seasonStartYear() === $seasonStartYear);

        return $this->history($teamRounds);
    }

    /**
     * The Season History up to a TeamRound: its earlier TeamRounds (see before()), then the TeamRound itself, last.
     */
    public function upTo(TeamRound $teamRound): SeasonHistory
    {
        // The TeamRound is loaded again, so the lineup relations below aren't set on the caller's model.
        $teamRounds = TeamRound::query()
            ->whereKey($teamRound->id)
            ->when($this->hasEarlierTeamRounds($teamRound), fn (Builder $query) => $query->orWhere($this->earlierThan($teamRound)))
            ->get();

        return $this->history($teamRounds);
    }

    /**
     * The Season History before a TeamRound: its Clubhouse's TeamRounds in its Season with a lower round number, since a
     * moved match keeps its round (§45). The game date doesn't matter. TeamRounds in the same round are left out, and so
     * are TeamRounds without a Season or round number. A TeamRound without a Season or round number has no earlier
     * TeamRounds. The round number has been required since before the 2026/2027 Season, so only older TeamRounds lack one.
     */
    public function before(TeamRound $teamRound): SeasonHistory
    {
        if (! $this->hasEarlierTeamRounds($teamRound)) {
            return new SeasonHistory([]);
        }

        return $this->history(TeamRound::query()->where($this->earlierThan($teamRound))->get());
    }

    private function hasEarlierTeamRounds(TeamRound $teamRound): bool
    {
        return $teamRound->season_id !== null && $teamRound->round !== null;
    }

    /**
     * @return Closure(Builder<TeamRound>): Builder<TeamRound>
     */
    private function earlierThan(TeamRound $teamRound): Closure
    {
        return fn (Builder $query) => $query
            ->where('clubhouse_id', $teamRound->clubhouse_id)
            ->where('season_id', $teamRound->season_id)
            ->where('round', '<', (int) $teamRound->round);
    }

    /**
     * @param  EloquentCollection<int, TeamRound>  $teamRounds
     */
    private function history(EloquentCollection $teamRounds): SeasonHistory
    {
        $teamRounds = $teamRounds
            ->load([
                'officialScenario',
                'squads.team.tier',
                // Drafts never count, so only categories that can be part of an Official Lineup are loaded.
                'squads.categories' => fn ($query) => $query
                    ->where(fn ($query) => $query
                        ->whereNull('team_round_scenario_id')
                        ->orWhereIn('team_round_scenario_id', TeamRoundScenario::query()->where('is_official', true)->select('id')))
                    ->orderBy('id'),
                'squads.categories.players' => fn ($query) => $query->orderBy('id'),
            ])
            // By round number, not game date (§45). Only load() can include TeamRounds without one; they come last.
            ->sortBy([
                fn (TeamRound $a, TeamRound $b) => [$a->round === null, (int) $a->round, $a->id] <=> [$b->round === null, (int) $b->round, $b->id],
            ])
            ->values();

        return new SeasonHistory($teamRounds->map(fn (TeamRound $teamRound) => $this->roundLineup($teamRound))->all());
    }

    private function roundLineup(TeamRound $teamRound): RoundLineup
    {
        $round = $teamRound->round === null ? null : (int) $teamRound->round;
        $official = $this->officialCategories($teamRound);

        $squads = [];
        $ladders = array_fill_keys(SeasonHistory::CATEGORIES, []);
        foreach ($teamRound->squads->values() as $index => $squad) {
            /** @var Squad $squad */
            $tier = $squad->effectiveTier();
            $lineupSquad = new LineupSquad(
                squadId: $squad->id,
                position: $index + 1,
                teamId: $squad->team_id === null ? null : (int) $squad->team_id,
                teamName: $squad->team === null ? $squad->name : $squad->team->name,
                tier: $tier,
                phase: SeasonPhase::fromRound($round, $tier),
                isPaperSquad: $squad->is_paper_squad,
            );
            $squads[] = $lineupSquad;

            $categories = $official
                ->where('squad_id', $squad->id)
                ->sortBy(fn (SquadCategory $category) => [(int) $category->name, $category->id]);
            foreach ($categories as $category) {
                $ladders[$category->category][] = new CategorySlot(
                    category: $category->category,
                    rank: count($ladders[$category->category]) + 1,
                    categoryName: $category->name,
                    squad: $lineupSquad,
                    players: collect($category->players)
                        ->map(fn (SquadMember $player) => new SlotPlayer((string) $player->member_ref_id, (string) $player->name, (string) $player->gender, $player->getVintage()))
                        ->values()
                        ->all(),
                );
            }
        }

        return new RoundLineup(
            teamRoundId: $teamRound->id,
            name: $teamRound->resolveName(),
            round: $round,
            gameDate: $teamRound->game_date === null ? null : Carbon::parse($teamRound->game_date)->toDateString(),
            squads: $squads,
            ladders: $ladders,
        );
    }

    /**
     * The categories of the TeamRound's Official Lineup: those of its official scenario, or for older TeamRounds
     * without one, those without a scenario. Same semantics as TeamRound::scopeVisibleToUser.
     *
     * @return Collection<int, SquadCategory>
     */
    private function officialCategories(TeamRound $teamRound): Collection
    {
        /** @var Collection<int, Squad> $squads */
        $squads = $teamRound->squads;
        $categories = $squads->flatMap(fn (Squad $squad) => $squad->categories);
        /** @var TeamRoundScenario|null $officialScenario */
        $officialScenario = $teamRound->officialScenario;

        return $officialScenario === null
            ? $categories->whereNull('team_round_scenario_id')
            : $categories->where('team_round_scenario_id', $officialScenario->id);
    }
}
