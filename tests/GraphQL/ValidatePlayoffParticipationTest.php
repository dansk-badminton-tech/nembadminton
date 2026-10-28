<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Season;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidatePlayoffParticipationTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Clubhouse $clubhouse;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Season::query()->firstOrCreate(['id' => 2026], ['season_name' => '2026/2027']);
        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = User::factory()->create(['clubhouse_id' => $this->clubhouse->id]);
        setPermissionsTeamId($this->clubhouse->id);
        $this->user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($this->user, 'api');
        Member::query()->create(['refId' => '0101011111', 'name' => 'Anders', 'gender' => 'M', 'birthday' => '2001-01-01']);
    }

    #[Test]
    public function a_liga_player_needs_four_earlier_dh_tournament_matches(): void
    {
        $current = $this->squad($this->round(10), 'Badmintonligaen');
        $this->place($current);
        foreach ([1, 2, 3] as $number) {
            $this->place($this->squad($this->round($number, '2026-10-0'.$number), '2. division'));
        }
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation', [[
            'squadIndex' => 0, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'REQUIREMENT_NOT_MET',
            'incompleteReasons' => [], 'matchCount' => 3, 'requiredMatchCount' => 4, 'playedByDeadline' => true,
            'uncountedRounds' => [],
        ]]);
        $this->place($this->squad($this->round(4, '2026-11-01'), 'Badmintonligaen'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'SATISFIED')
            ->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 4);
    }

    #[Test]
    public function the_requirement_covers_liga_and_1_division_squads_in_the_playoffs_and_qualification(): void
    {
        foreach ([
            ['Badmintonligaen', [9 => [0, 'NOT_APPLICABLE'], 10 => [4, 'REQUIREMENT_NOT_MET'], 12 => [4, 'REQUIREMENT_NOT_MET']]],
            ['1. division', [7 => [0, 'NOT_APPLICABLE'], 8 => [3, 'REQUIREMENT_NOT_MET'], 12 => [3, 'REQUIREMENT_NOT_MET']]],
            ['2. division', [11 => [0, 'NOT_APPLICABLE'], 12 => [0, 'NOT_APPLICABLE']]],
            ['Danmarksserien', [12 => [0, 'NOT_APPLICABLE']]],
            ['Serie 1', [12 => [0, 'NOT_APPLICABLE']]],
        ] as [$tier, $rounds]) {
            foreach ($rounds as $number => [$required, $status]) {
                $squad = $this->squad($this->round($number), $tier);
                $this->place($squad);
                $this->check($squad)
                    ->assertJsonPath('data.validatePlayoffParticipation.0.status', $status, "$tier round $number")
                    ->assertJsonPath('data.validatePlayoffParticipation.0.requiredMatchCount', $required);
                $squad->teamRound->delete();
            }
        }
    }

    #[Test]
    public function a_paper_squad_is_not_checked(): void
    {
        $squad = $this->squad($this->round(10), 'Badmintonligaen', ['is_paper_squad' => true]);
        $this->place($squad);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0', [
            'squadIndex' => 0, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'NOT_APPLICABLE',
            'incompleteReasons' => [], 'matchCount' => 0, 'requiredMatchCount' => 0, 'playedByDeadline' => null,
            'uncountedRounds' => [],
        ]);
    }

    #[Test]
    public function every_dh_tournament_tier_counts_but_regional_series_do_not(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $this->place($this->squad($this->round(1, '2026-10-01'), '3. division'));
        $this->place($this->squad($this->round(2), 'Danmarksserien'));
        $this->place($this->squad($this->round(3), 'Serie 1'));
        $this->place($this->squad($this->round(4), 'Københavnsserien'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 2);
        $this->place($this->squad($this->round(5), 'Badmintonligaen'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'SATISFIED')
            ->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 3)
            ->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', true);
    }

    #[Test]
    public function a_paper_squad_counts_toward_the_matches_but_not_the_20_october_deadline(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $paper = $this->squad($this->round(1, '2026-09-20'), '1. division', ['is_paper_squad' => true]);
        $this->place($paper);
        $this->place($this->squad($this->round(5, '2026-11-20'), '1. division'));
        $this->place($this->squad($this->round(6, '2026-12-20'), '1. division'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 3)
            ->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', false);
        $paper->update(['is_paper_squad' => false]);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'SATISFIED')
            ->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', true);
    }

    #[Test]
    public function the_deadline_is_20_october_of_the_seasons_start_year_inclusive(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $this->place($this->squad($this->round(5, '2026-10-21'), '1. division'));
        $this->place($this->squad($this->round(6, '2026-11-01'), '1. division'));
        $earliest = $this->round(7, '2025-10-01');
        $this->place($this->squad($earliest, '2. division'));
        // A date in the year before the Season's start year still meets it; the deadline is a date, not a window.
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', true);
        $earliest->update(['game_date' => '2026-10-21']);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 3)
            ->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', false);
        $earliest->update(['game_date' => '2026-10-20']);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'SATISFIED');
    }

    #[Test]
    public function the_squads_own_date_comes_before_the_team_rounds(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $moved = $this->squad($this->round(1, '2026-09-20'), '1. division', ['playing_datetime' => '2026-10-25 19:00:00']);
        $this->place($moved);
        $this->place($this->squad($this->round(5, '2026-11-20'), '1. division'));
        $this->place($this->squad($this->round(6, '2026-12-20'), '1. division'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', false);
        $moved->update(['playing_datetime' => '2026-10-20 19:00:00']);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', true);
    }

    #[Test]
    public function only_earlier_rounds_of_the_clubhouses_season_count(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $this->place($this->squad($this->round(1, '2026-10-01'), '1. division'));
        $this->place($this->squad($this->round(2), '1. division'));
        $this->place($this->squad($current->teamRound, '2. division'));
        $this->place($this->squad($this->round(8), '2. division'));
        $this->place($this->squad($this->round(9), '2. division'));
        Season::query()->firstOrCreate(['id' => 2025], ['season_name' => '2025/2026']);
        $this->place($this->squad($this->round(3, '2025-10-01', ['season_id' => 2025]), '1. division'));
        $foreign = Clubhouse::factory()->create();
        $this->place($this->squad($this->round(3, '2026-10-01', ['clubhouse_id' => $foreign->id]), '1. division'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 2)
            ->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validatePlayoffParticipation.1.status', 'NOT_APPLICABLE');
    }

    #[Test]
    public function a_missing_round_season_or_tier_on_the_current_squad_cannot_be_verified(): void
    {
        $round = $this->round(10);
        $squad = $this->squad($round, 'Badmintonligaen');
        $this->place($squad);
        $round->update(['round' => null]);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'CANNOT_VERIFY')
            ->assertJsonPath('data.validatePlayoffParticipation.0.incompleteReasons', ['MISSING_ROUND'])
            ->assertJsonPath('data.validatePlayoffParticipation.0.requiredMatchCount', 4);
        $round->update(['round' => 10, 'season_id' => null]);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0.incompleteReasons', ['MISSING_SEASON']);
        $round->update(['season_id' => 2026]);
        $squad->update(['team_id' => null, 'tier' => 'Ukendt række']);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'CANNOT_VERIFY')
            ->assertJsonPath('data.validatePlayoffParticipation.0.incompleteReasons', ['UNKNOWN_TIER'])
            ->assertJsonPath('data.validatePlayoffParticipation.0.requiredMatchCount', 0);
        // Every tier the requirement covers is still in the group stage.
        $round->update(['round' => 7]);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'NOT_APPLICABLE');
        $round->update(['round' => null]);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0.incompleteReasons', ['MISSING_ROUND', 'UNKNOWN_TIER']);
        // A known tier the requirement doesn't cover needs no round number.
        $squad->update(['tier' => '2. division']);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'NOT_APPLICABLE');
    }

    #[Test]
    public function an_earlier_squad_with_unknown_tier_is_open_only_when_it_could_meet_the_requirement(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $this->place($this->squad($this->round(1, '2026-10-01'), '1. division'));
        $unknown = $this->round(2, '2026-11-01', ['name' => 'Ukendt række runde']);
        $unknownSquad = $this->squad($unknown, 'Ukendt række');
        $this->place($unknownSquad);
        // Even counted, 2 of 3.
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validatePlayoffParticipation.0.matchCount', 1);
        $this->place($this->squad($this->round(3, '2026-11-08'), '1. division'));
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0', [
            'squadIndex' => 0, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'CANNOT_VERIFY',
            'incompleteReasons' => ['UNKNOWN_HISTORY_TIER'], 'matchCount' => 2, 'requiredMatchCount' => 3,
            'playedByDeadline' => true,
            'uncountedRounds' => [['teamRoundId' => $unknown->id, 'name' => 'Ukendt række runde', 'round' => 2]],
        ]);
        $unknownSquad->team->update(['custom_tier_name' => 'Serie 1']);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET');
    }

    #[Test]
    public function an_earlier_squad_without_a_date_leaves_the_deadline_open(): void
    {
        $current = $this->squad($this->round(8), '1. division');
        $this->place($current);
        $undated = $this->round(1, '2026-10-01', ['name' => 'Uden dato']);
        $this->place($this->squad($undated, '1. division'));
        $this->place($this->squad($this->round(2, '2026-11-01'), '1. division'));
        $this->place($this->squad($this->round(3, '2026-11-08'), '1. division'));
        $undated->update(['game_date' => null]);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'CANNOT_VERIFY')
            ->assertJsonPath('data.validatePlayoffParticipation.0.incompleteReasons', ['MISSING_HISTORY_DATE'])
            ->assertJsonPath('data.validatePlayoffParticipation.0.playedByDeadline', null)
            ->assertJsonPath('data.validatePlayoffParticipation.0.uncountedRounds.*.teamRoundId', [$undated->id]);
        // Too few matches is unmet whatever the date.
        $current->teamRound->update(['round' => 10]);
        $current->team->update(['custom_tier_name' => 'Badmintonligaen']);
        $this->check($current)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validatePlayoffParticipation.0.incompleteReasons', [])
            ->assertJsonPath('data.validatePlayoffParticipation.0.uncountedRounds', []);
    }

    #[Test]
    public function a_scenario_is_checked_and_another_clubhouses_round_or_scenario_is_rejected(): void
    {
        $round = $this->round(10);
        $squad = $this->squad($round, 'Badmintonligaen');
        $draft = $round->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);
        $this->place($squad, ['team_round_scenario_id' => $draft->id]);
        $this->check($squad)->assertJsonPath('data.validatePlayoffParticipation', []);
        $this->check($squad, $draft->id)->assertJsonPath('data.validatePlayoffParticipation.0.status', 'REQUIREMENT_NOT_MET');

        $query = 'mutation($id: ID!, $scenario: ID) { validatePlayoffParticipation(teamRoundId: $id, scenarioId: $scenario) { refId } }';
        $foreignRound = $this->round(10, '2027-03-20', ['clubhouse_id' => Clubhouse::factory()->create()->id]);
        $foreignDraft = $foreignRound->scenarios()->create(['name' => 'Hemmelig', 'is_official' => false]);
        $this->graphQL($query, ['id' => $round->id, 'scenario' => $foreignDraft->id])
            ->assertGraphQLErrorMessage('This action is unauthorized.');

        $foreignUser = User::factory()->create(['clubhouse_id' => $foreignRound->clubhouse_id]);
        setPermissionsTeamId($foreignUser->clubhouse_id);
        $foreignUser->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($foreignUser, 'api');
        $this->graphQL($query, ['id' => $round->id, 'scenario' => $draft->id])
            ->assertGraphQLErrorMessage('This action is unauthorized.')->assertDontSee('Anders');
        auth('api')->forgetUser();
        $this->graphQL($query, ['id' => $round->id])->assertGraphQLErrorMessage('Unauthenticated.');
    }

    private function round(int $number, string $gameDate = '2027-03-20', array $attributes = []): TeamRound
    {
        return TeamRound::factory()->create(array_merge([
            'clubhouse_id' => $this->clubhouse->id, 'user_id' => $this->user->id,
            'season_id' => 2026, 'round' => $number, 'game_date' => $gameDate,
        ], $attributes));
    }

    private function squad(TeamRound $round, string $tier, array $attributes = []): Squad
    {
        $team = Team::factory()->withCustomTier($tier)->create(['clubhouse_id' => $round->clubhouse_id]);

        return Squad::query()->create(array_merge(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10], $attributes));
    }

    private function place(Squad $squad, array $attributes = []): SquadCategory
    {
        $category = $squad->categories()->create(array_merge(['category' => 'HS', 'name' => '1. HS'], $attributes));
        $category->players()->create(['name' => 'Anders', 'gender' => 'M', 'member_ref_id' => '0101011111']);

        return $category;
    }

    private function check(Squad $squad, ?int $scenarioId = null): TestResponse
    {
        return $this->graphQL('mutation($id: ID!, $scenario: ID) {
            validatePlayoffParticipation(teamRoundId: $id, scenarioId: $scenario) {
                squadIndex refId name status incompleteReasons matchCount requiredMatchCount playedByDeadline
                uncountedRounds { teamRoundId name round }
            }
        }', ['id' => $squad->team_round_id, 'scenario' => $scenarioId])->assertGraphQLErrorFree();
    }
}
