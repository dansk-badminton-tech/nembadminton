<?php

declare(strict_types=1);

namespace Tests\Unit\SeasonHistory;

use App\Enums\SeasonPhase;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Season;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\TournamentTier;
use App\Models\User;
use App\SeasonHistory\CategoryPlacement;
use App\SeasonHistory\CategorySlot;
use App\SeasonHistory\Placement;
use App\SeasonHistory\RoundLineup;
use App\SeasonHistory\SeasonHistory;
use App\SeasonHistory\SeasonHistoryLoader;
use App\SeasonHistory\UnknownPhaseException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeasonHistoryTest extends TestCase
{
    use RefreshDatabase;

    private const SEASON = 2026;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Clubhouse $clubhouse;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Season::query()->firstOrCreate(['id' => self::SEASON], ['season_name' => '2026/2027']);
        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = User::factory()->create(['clubhouse_id' => $this->clubhouse->id]);
    }

    #[Test]
    public function category_slots_are_ranked_in_squad_order_then_category_number(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $this->member('0101013333', 'Carl');
        $this->member('0101014444', 'Dan');
        $round = $this->teamRound(round: 1);
        // Created out of order to show the ranking follows Squad order and category number, not insertion.
        $team2 = $this->squad($round, 'Klub 2', order: 2);
        $team1 = $this->squad($round, 'Klub 1', order: 1);
        $this->category($team1, 'HS', '2. HS', ['0101012222']);
        $this->category($team1, 'HS', '1. HS', ['0101011111']);
        $this->category($team2, 'HS', '1. HS', ['0101013333']);
        $this->category($team2, 'HS', '2. HS', ['0101014444']);

        $history = $this->load();

        $ladder = $history->rounds()[0]->ladder('HS');
        $this->assertSame(
            [
                [1, 'Klub 1', '1. HS', ['Anders']],
                [2, 'Klub 1', '2. HS', ['Bo']],
                [3, 'Klub 2', '1. HS', ['Carl']],
                [4, 'Klub 2', '2. HS', ['Dan']],
            ],
            array_map(fn (CategorySlot $slot) => [
                $slot->rank,
                $slot->squad->teamName,
                $slot->categoryName,
                array_map(fn ($player) => $player->name, $slot->players),
            ], $ladder),
        );
        $this->assertSame([], $history->rounds()[0]->ladder('DS'));
    }

    #[Test]
    public function partners_in_doubles_share_their_category_slot_and_name_each_other(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $this->member('0101013333', 'Carl');
        $this->member('0101014444', 'Dan');
        $this->member('0202021111', 'Eva', 'K');
        $round = $this->teamRound(round: 1);
        $team1 = $this->squad($round, 'Klub 1', order: 1);
        $this->category($team1, 'HD', '1. HD', ['0101011111', '0101012222']);
        $this->category($team1, 'HD', '2. HD', ['0101013333', '0101014444']);
        $this->category($team1, 'MD', '1. MD', ['0101013333', '0202021111']);
        $this->category($team1, 'HS', '1. HS', ['0101013333']);

        $history = $this->load();

        $this->assertSame([['HD', 1, 'Bo']], $this->categoriesOf($history, '0101011111'));
        $this->assertSame([['HD', 1, 'Anders']], $this->categoriesOf($history, '0101012222'));
        $this->assertSame(
            [['HS', 1, null], ['HD', 2, 'Dan'], ['MD', 1, 'Eva']],
            $this->categoriesOf($history, '0101013333'),
        );
        $this->assertSame([['MD', 1, 'Carl']], $this->categoriesOf($history, '0202021111'));
    }

    #[Test]
    public function a_member_has_one_placement_per_team_round_they_stand_in(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $hold1 = Team::factory()->withCustomTier('2. division')->create(['name' => 'Klub 1', 'clubhouse_id' => $this->clubhouse->id]);
        $hold2 = Team::factory()->withCustomTier('Danmarksserien')->create(['name' => 'Klub 2', 'clubhouse_id' => $this->clubhouse->id]);
        foreach ([1 => '2026-09-20', 2 => '2026-10-04', 3 => '2026-10-25'] as $roundNumber => $date) {
            $round = $this->teamRound(round: $roundNumber, attributes: ['game_date' => $date]);
            $squad1 = $this->squad($round, 'ignored', order: 1, team: $hold1);
            $squad2 = $this->squad($round, 'ignored', order: 2, team: $hold2);
            $this->category($squad1, 'HS', '1. HS', ['0101012222']);
            $this->category($roundNumber === 3 ? $squad2 : $squad1, 'HS', $roundNumber === 3 ? '1. HS' : '2. HS', ['0101011111']);
        }

        $placements = $this->load()->placementsFor('0101011111');

        $this->assertSame(
            [
                ['Runde 1', 1, '2026-09-20', 'Klub 1', $hold1->id, 1, '2. division', 'grundspil', [['HS', 2, '2. HS']]],
                ['Runde 2', 2, '2026-10-04', 'Klub 1', $hold1->id, 1, '2. division', 'grundspil', [['HS', 2, '2. HS']]],
                ['Runde 3', 3, '2026-10-25', 'Klub 2', $hold2->id, 2, 'Danmarksserien', 'grundspil', [['HS', 2, '1. HS']]],
            ],
            array_map(fn (Placement $placement) => [
                $placement->round->name,
                $placement->round->round,
                $placement->round->gameDate,
                $placement->squad->teamName,
                $placement->squad->teamId,
                $placement->squad->position,
                $placement->squad->tier,
                $placement->phase()?->value,
                array_map(fn (CategoryPlacement $category) => [
                    $category->slot->category,
                    $category->slot->rank,
                    $category->slot->categoryName,
                ], $placement->categories),
            ], $placements),
        );
    }

    #[Test]
    public function a_member_on_two_squads_in_one_team_round_has_two_placements(): void
    {
        $this->member('0101011111', 'Anders');
        $round = $this->teamRound(round: 1);
        $this->category($this->squad($round, 'Klub 1', order: 1), 'HS', '1. HS', ['0101011111']);
        $this->category($this->squad($round, 'Klub 2', order: 2), 'HS', '1. HS', ['0101011111']);

        $placements = $this->load()->placementsFor('0101011111');

        $this->assertSame(
            [['Klub 1', 1], ['Klub 2', 2]],
            array_map(fn (Placement $placement) => [$placement->squad->teamName, $placement->categories[0]->slot->rank], $placements),
        );
    }

    #[Test]
    public function an_empty_seat_keeps_its_slot_in_the_ladder(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $round = $this->teamRound(round: 1);
        $squad = $this->squad($round, 'Klub 1', order: 1);
        $this->category($squad, 'HD', '1. HD', ['0101011111']);
        $this->category($squad, 'HD', '2. HD', []);
        $this->category($squad, 'HD', '3. HD', ['0101012222']);

        $history = $this->load();

        $this->assertSame([['HD', 1, null]], $this->categoriesOf($history, '0101011111'));
        $this->assertSame([['HD', 3, null]], $this->categoriesOf($history, '0101012222'));
    }

    #[Test]
    public function only_the_official_lineup_counts_and_older_rounds_without_scenarios_count(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $this->member('0101013333', 'Carl');

        $withScenarios = $this->teamRound(round: 1, attributes: ['game_date' => '2026-09-20']);
        $official = $withScenarios->scenarios()->create(['name' => 'Plan A', 'is_official' => true]);
        $draft = $withScenarios->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);
        $squad = $this->squad($withScenarios, 'Klub 1', order: 1);
        $this->category($squad, 'HS', '1. HS', ['0101011111'], $official->id);
        $this->category($squad, 'HS', '1. HS', ['0101012222'], $draft->id);

        $withoutScenarios = $this->teamRound(round: 2, attributes: ['game_date' => '2026-10-04']);
        $this->category($this->squad($withoutScenarios, 'Klub 1', order: 1), 'HS', '1. HS', ['0101013333']);

        $history = $this->load();

        $this->assertSame(['Runde 1'], $this->roundNamesOf($history, '0101011111'));
        $this->assertSame([], $this->roundNamesOf($history, '0101012222'));
        $this->assertSame(['Runde 2'], $this->roundNamesOf($history, '0101013333'));
    }

    #[Test]
    public function team_rounds_of_another_clubhouse_or_season_do_not_count(): void
    {
        $this->member('0101011111', 'Anders');
        Season::query()->firstOrCreate(['id' => 2025], ['season_name' => '2025/2026']);
        $otherClubhouse = Clubhouse::factory()->create();

        $rounds = [
            'this season' => $this->teamRound(round: 1, attributes: ['name' => 'this season', 'game_date' => '2026-09-20']),
            'this season by date' => $this->teamRound(round: 2, attributes: ['name' => 'this season by date', 'season_id' => null, 'game_date' => '2027-03-01']),
            'last season' => $this->teamRound(round: 3, attributes: ['name' => 'last season', 'season_id' => 2025, 'game_date' => '2026-03-01']),
            'last season by date' => $this->teamRound(round: 4, attributes: ['name' => 'last season by date', 'season_id' => null, 'game_date' => '2026-06-30']),
            'other clubhouse' => $this->teamRound(round: 5, attributes: ['name' => 'other clubhouse', 'clubhouse_id' => $otherClubhouse->id]),
        ];
        foreach ($rounds as $round) {
            $this->category($this->squad($round, 'Klub 1', order: 1), 'HS', '1. HS', ['0101011111']);
        }

        $history = $this->load();

        $this->assertSame(['this season', 'this season by date'], $this->roundNamesOf($history, '0101011111'));
    }

    #[Test]
    public function it_answers_the_lookups_the_rules_need(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $hold1 = Team::factory()->withCustomTier('1. division')->create(['name' => 'Klub 1', 'clubhouse_id' => $this->clubhouse->id]);
        $hold2 = Team::factory()->withCustomTier('2. division')->create(['name' => 'Klub 2', 'clubhouse_id' => $this->clubhouse->id]);
        $hold3 = Team::factory()->withCustomTier('3. division')->create(['name' => 'Klub 3', 'clubhouse_id' => $this->clubhouse->id]);

        // Round 1: Anders on Klub 1.
        $round1 = $this->teamRound(round: 1, attributes: ['game_date' => '2026-09-20']);
        $this->category($this->squad($round1, 'x', order: 1, team: $hold1), 'HS', '1. HS', ['0101011111']);
        $this->squad($round1, 'x', order: 2, team: $hold2);
        // Round 2: Anders on Klub 2.
        $round2 = $this->teamRound(round: 2, attributes: ['game_date' => '2026-10-04']);
        $this->squad($round2, 'x', order: 1, team: $hold1);
        $this->category($this->squad($round2, 'x', order: 2, team: $hold2), 'HD', '1. HD', ['0101011111', '0101012222']);
        // Round 3: Klub 3 stands above Klub 2 in this round's Squad order; Anders plays on Klub 3.
        $round3 = $this->teamRound(round: 3, attributes: ['game_date' => '2026-10-25']);
        $this->category($this->squad($round3, 'x', order: 1, team: $hold3), 'MD', '1. MD', ['0101011111']);
        $this->squad($round3, 'x', order: 2, team: $hold2);
        // Round 8: slutspil in 2. division; Anders plays on Klub 2.
        $round8 = $this->teamRound(round: 8, attributes: ['game_date' => '2027-03-01']);
        $this->category($this->squad($round8, 'x', order: 1, team: $hold2), 'HS', '1. HS', ['0101011111']);
        // A round without Klub 2: it can't be compared with Klub 2, so it never counts as same or lower.
        $round9 = $this->teamRound(round: 9, attributes: ['game_date' => '2027-03-15']);
        $this->category($this->squad($round9, 'x', order: 1, team: $hold3), 'HS', '1. HS', ['0101011111']);

        $history = $this->load();

        $this->assertSame(1, $history->teamMatchCount('0101011111', $hold1->id));
        $this->assertSame(2, $history->teamMatchCount('0101011111', $hold2->id));
        $this->assertSame(1, $history->teamMatchCount('0101011111', $hold2->id, SeasonPhase::GRUNDSPIL));
        $this->assertSame(0, $history->teamMatchCount('0101012222', $hold1->id));

        // Klub 1 has a Squad only in rounds 1 and 2.
        $this->assertSame(2, $history->matchCountOnSameOrLowerTeam('0101011111', $hold1->id));
        $this->assertSame(2, $history->matchCountOnSameOrLowerTeam('0101011111', $hold2->id));
        $this->assertSame(1, $history->matchCountOnSameOrLowerTeam('0101011111', $hold2->id, SeasonPhase::GRUNDSPIL));
        $this->assertSame(2, $history->matchCountOnSameOrLowerTeam('0101011111', $hold3->id));

        $this->assertSame(
            [[1, '2026-09-20'], [2, '2026-10-04'], [3, '2026-10-25'], [8, '2027-03-01'], [9, '2027-03-15']],
            array_map(fn (RoundLineup $round) => [$round->round, $round->gameDate], $history->playedRounds('0101011111')),
        );
        $this->assertSame([[2, '2026-10-04']], array_map(fn (RoundLineup $round) => [$round->round, $round->gameDate], $history->playedRounds('0101012222')));

        $this->assertSame(['HS', 'HD', 'MD'], $history->playedCategories('0101011111'));
        $this->assertSame(['HS'], $history->playedCategories('0101011111', SeasonPhase::SLUTSPIL));
    }

    #[Test]
    public function a_team_round_without_a_round_number_has_an_unknown_phase_that_rules_report(): void
    {
        $this->member('0101011111', 'Anders');
        $known = $this->teamRound(round: 1, attributes: ['game_date' => '2026-09-20']);
        $this->category($this->squad($known, 'Klub 1', order: 1), 'HS', '1. HS', ['0101011111']);
        $unknown = $this->teamRound(round: null, attributes: ['name' => 'Ekstra runde', 'game_date' => '2026-10-04']);
        $this->category($this->squad($unknown, 'Klub 1', order: 1), 'HS', '1. HS', ['0101011111']);

        $history = $this->load();

        $placements = $history->placementsFor('0101011111');
        $this->assertNull($placements[1]->phase());
        $this->assertSame(1, $placements[1]->categories[0]->slot->rank);
        $this->assertSame([$unknown->id], array_map(fn (RoundLineup $round) => $round->teamRoundId, $history->roundsWithUnknownPhase()));
        $this->assertSame(2, count($history->playedRounds('0101011111')));

        try {
            $history->playedRounds('0101011111', SeasonPhase::GRUNDSPIL);
            $this->fail('Expected an UnknownPhaseException.');
        } catch (UnknownPhaseException $exception) {
            $this->assertSame($unknown->id, $exception->teamRoundId);
            $this->assertStringContainsString('Ekstra runde', $exception->getMessage());
        }
    }

    #[Test]
    public function a_bigger_season_loads_in_the_same_number_of_queries(): void
    {
        $this->member('0101011111', 'Anders');
        $this->member('0101012222', 'Bo');
        $this->seasonOfRounds(1);
        $smallSeasonQueries = $this->countQueries(fn () => $this->load()->placementsFor('0101011111'));

        $this->seasonOfRounds(6);
        $bigSeasonQueries = $this->countQueries(fn () => $this->load()->placementsFor('0101011111'));

        $this->assertCount(7, $this->load()->placementsFor('0101011111'));
        $this->assertSame($smallSeasonQueries, $bigSeasonQueries);
    }

    private function seasonOfRounds(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $round = $this->teamRound(round: 1);
            $official = $round->scenarios()->create(['name' => 'Plan A', 'is_official' => true]);
            $team = Team::factory()->withTier(TournamentTier::query()->firstOrCreate(['tier_name' => '2. division']))
                ->create(['clubhouse_id' => $this->clubhouse->id]);
            foreach ([1, 2, 3] as $order) {
                $squad = $this->squad($round, "Klub $order", order: $order, team: $order === 1 ? $team : null);
                if ($order === 1) {
                    $this->category($squad, 'HD', '1. HD', ['0101011111', '0101012222'], $official->id);
                }
                $this->category($squad, 'HS', '1. HS', ['0101012222'], $official->id);
            }
        }
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }

    /**
     * @return list<string>
     */
    private function roundNamesOf(SeasonHistory $history, string $refId): array
    {
        return array_map(fn (Placement $placement) => $placement->round->name, $history->placementsFor($refId));
    }

    /**
     * The categories of a Member's only placement as [category, Kategoriplads, partner name].
     *
     * @return list<array{string, int, string|null}>
     */
    private function categoriesOf(SeasonHistory $history, string $refId): array
    {
        $placements = $history->placementsFor($refId);
        $this->assertCount(1, $placements);

        return array_map(fn (CategoryPlacement $placement) => [
            $placement->slot->category,
            $placement->slot->rank,
            $placement->partner?->name,
        ], $placements[0]->categories);
    }

    private function load(?int $season = self::SEASON): SeasonHistory
    {
        return app(SeasonHistoryLoader::class)->load($this->clubhouse, $season);
    }

    private function member(string $refId, string $name, string $gender = 'M'): Member
    {
        return Member::query()->create([
            'refId' => $refId,
            'name' => $name,
            'gender' => $gender,
            'birthday' => '2001-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function teamRound(?int $round, array $attributes = []): TeamRound
    {
        return TeamRound::factory()->create([
            'clubhouse_id' => $this->clubhouse->id,
            'user_id' => $this->user->id,
            'season_id' => self::SEASON,
            'round' => $round,
            'name' => null,
            ...$attributes,
        ]);
    }

    private function squad(TeamRound $round, string $name, int $order, ?Team $team = null, ?string $tier = null): Squad
    {
        $squad = Squad::query()->create([
            'team_round_id' => $round->id,
            'playerLimit' => 10,
            'name' => $name,
            'team_id' => $team?->id,
            'tier' => $tier,
        ]);
        // Sortable assigns the next order on create, so set the wanted order afterwards.
        $squad->update(['order' => $order]);

        return $squad;
    }

    /**
     * @param  list<string>  $refIds
     */
    private function category(Squad $squad, string $category, string $name, array $refIds, ?int $scenarioId = null): void
    {
        $squadCategory = $squad->categories()->create([
            'category' => $category,
            'name' => $name,
            'team_round_scenario_id' => $scenarioId,
        ]);
        foreach ($refIds as $refId) {
            $member = Member::query()->where('refId', $refId)->firstOrFail();
            $squadCategory->players()->create([
                'name' => $member->name,
                'gender' => $member->gender,
                'member_ref_id' => $refId,
            ]);
        }
    }
}
