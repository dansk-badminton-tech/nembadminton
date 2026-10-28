<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Season;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ValidateQualificationEligibilityTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Clubhouse $clubhouse;

    private User $user;

    private Team $team;

    protected function setUp(): void
    {
        parent::setUp();
        Season::query()->firstOrCreate(['id' => 2026], ['season_name' => '2026/2027']);
        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = User::factory()->create(['clubhouse_id' => $this->clubhouse->id]);
        setPermissionsTeamId($this->clubhouse->id);
        $this->user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($this->user, 'api');
        $this->team = Team::factory()->withCustomTier('2. division')->create(['clubhouse_id' => $this->clubhouse->id]);
        Member::query()->create(['refId' => '0101011111', 'name' => 'Anders', 'gender' => 'M', 'birthday' => '2001-01-01']);
    }

    #[Test]
    public function two_appearances_need_one_in_rounds_seven_through_eleven_but_three_always_suffice(): void
    {
        $current = $this->squad($this->round());
        $this->place($current);
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'REQUIREMENT_NOT_MET');
        $this->place($this->squad($this->round(['round' => 1])));
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'REQUIREMENT_NOT_MET');
        $second = $this->round(['round' => 6]);
        $squad = $this->squad($second);
        $this->place($squad);
        $this->place($squad, ['category' => 'HD', 'name' => '1. HD']);
        // Dates in the qualification match's calendar year do not establish the omitted alternative.
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'REQUIREMENT_NOT_MET');
        foreach ([7, 8, 9, 10, 11] as $number) {
            $second->update(['round' => $number]);
            $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'SATISFIED');
        }
        $second->update(['round' => 6]);
        $this->place($this->squad($this->round(['round' => 2])));
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'SATISFIED');
    }

    #[Test]
    public function applicability_follows_the_original_team_tier_not_the_qualification_match_format(): void
    {
        foreach (['Badmintonligaen', '1. division', '2. division', '3. division', 'Danmarksserien', 'Serie 1'] as $tier) {
            $team = Team::factory()->withCustomTier($tier)->create(['clubhouse_id' => $this->clubhouse->id]);
            foreach ([1, 7, 8, 11, 12] as $number) {
                $round = $this->round(['round' => $number]);
                $current = $this->squad($round, $team);
                $current->update(['tier' => '1. division', 'playerLimit' => 8]);
                $this->place($current);
                $expected = $number === 12 && in_array($tier, ['2. division', '3. division', 'Danmarksserien'], true)
                    ? 'REQUIREMENT_NOT_MET' : 'NOT_APPLICABLE';
                $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', $expected);
                $current->update(['is_paper_squad' => true]);
                $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'NOT_APPLICABLE');
                $round->delete();
            }
        }
    }

    #[Test]
    public function missing_current_context_is_explicit_but_dates_are_not_required(): void
    {
        foreach (['round' => 'MISSING_ROUND', 'season_id' => 'MISSING_SEASON'] as $field => $reason) {
            $current = $this->squad($this->round([$field => null]));
            $this->place($current);
            $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'CANNOT_VERIFY')
                ->assertJsonPath('data.validateQualificationEligibility.0.incompleteReasons', [$reason]);
        }
        $current = $this->squad($this->round(['game_date' => null]));
        $this->place($current);
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'REQUIREMENT_NOT_MET');
        $current->update(['team_id' => null, 'tier' => '2. division']);
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.incompleteReasons', ['MISSING_TEAM']);
        $current->update(['tier' => 'Ukendt']);
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.incompleteReasons', ['UNKNOWN_TIER', 'MISSING_TEAM']);
    }

    #[Test]
    public function incomparable_history_names_the_round_once_unless_known_history_already_suffices(): void
    {
        $current = $this->squad($this->round());
        $this->place($current);
        $historical = $this->round(['round' => 2, 'name' => 'Uden holdlink']);
        $unlinked = $this->squad($historical);
        $unlinked->update(['team_id' => null]);
        $this->place($unlinked);
        $this->place($unlinked, ['category' => 'HD']);
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0', [
            'squadIndex' => 0, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'CANNOT_VERIFY',
            'incompleteReasons' => ['INCOMPARABLE_HISTORY'],
            'uncountedRounds' => [['teamRoundId' => $historical->id, 'name' => 'Uden holdlink', 'round' => 2]],
        ]);
        foreach ([1, 7] as $number) {
            $this->place($this->squad($this->round(['round' => $number])));
        }
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'SATISFIED')
            ->assertJsonPath('data.validateQualificationEligibility.0.incompleteReasons', [])
            ->assertJsonPath('data.validateQualificationEligibility.0.uncountedRounds', []);
    }

    #[Test]
    public function only_earlier_official_appearances_on_historically_same_or_lower_teams_count(): void
    {
        Season::query()->firstOrCreate(['id' => 2025], ['season_name' => '2025/2026']);
        $current = $this->squad($this->round());
        $this->place($current);
        foreach ([
            ['round' => 12], ['round' => 13], ['round' => null],
            ['round' => 7, 'season_id' => 2025],
            ['round' => 8, 'clubhouse_id' => Clubhouse::factory()->create()->id],
        ] as $attributes) {
            $this->place($this->squad($this->round($attributes)));
        }
        $earlier = $this->round(['round' => 7]);
        $higherTeam = Team::factory()->withCustomTier('Danmarksserien')->create(['clubhouse_id' => $this->clubhouse->id]);
        $higher = $this->squad($earlier, $higherTeam);
        $this->place($higher);
        $same = $this->squad($earlier);
        $draft = $earlier->scenarios()->create(['name' => 'Kladde', 'is_official' => false]);
        $this->place($same, ['team_round_scenario_id' => $draft->id]);
        $this->place($this->squad($this->round(['round' => 1])));
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'REQUIREMENT_NOT_MET');
        $draft->update(['is_official' => true]);
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'SATISFIED');
        $same->update(['order' => 0]);
        $higher->categories()->update(['team_round_scenario_id' => $draft->id]);
        $same->categories()->delete();
        $this->check($current)->assertJsonPath('data.validateQualificationEligibility.0.status', 'SATISFIED');
    }

    #[Test]
    public function checks_the_selected_scenario_and_defaults_to_the_official_lineup(): void
    {
        $round = $this->round();
        $current = $this->squad($round);
        $official = $round->scenarios()->create(['name' => 'Officiel', 'is_official' => true]);
        $draft = $round->scenarios()->create(['name' => 'Tom kladde', 'is_official' => false]);
        $this->place($current, ['team_round_scenario_id' => $official->id]);
        $this->place($current, ['team_round_scenario_id' => $official->id, 'category' => 'HD']);
        $this->check($current)->assertJsonCount(1, 'data.validateQualificationEligibility');
        $this->check($current, $draft->id)->assertJsonPath('data.validateQualificationEligibility', []);
        $this->place($current, ['team_round_scenario_id' => $draft->id]);
        $this->check($current, $draft->id)->assertJsonPath('data.validateQualificationEligibility.0.status', 'REQUIREMENT_NOT_MET');
    }

    #[Test]
    public function foreign_rounds_and_scenarios_and_public_access_are_denied(): void
    {
        $current = $this->squad($this->round());
        $this->place($current);
        $query = 'mutation($id: ID!, $scenario: ID) {
            validateQualificationEligibility(teamRoundId: $id, scenarioId: $scenario) { name status uncountedRounds { name } }
        }';
        foreach ([$this->round(), $this->round(['clubhouse_id' => Clubhouse::factory()->create()->id])] as $other) {
            $scenario = $other->scenarios()->create(['name' => 'Private kladde', 'is_official' => false]);
            $this->graphQL($query, ['id' => $current->team_round_id, 'scenario' => $scenario->id])
                ->assertGraphQLErrorMessage('This action is unauthorized.')->assertDontSee('Anders');
        }
        $this->graphQL($query, ['id' => $current->team_round_id, 'scenario' => 999999])
            ->assertGraphQLErrorMessage('This action is unauthorized.');
        $user = User::factory()->create(['clubhouse_id' => Clubhouse::factory()->create()->id]);
        setPermissionsTeamId($user->clubhouse_id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($user, 'api');
        $this->graphQL($query, ['id' => $current->team_round_id])
            ->assertGraphQLErrorMessage('This action is unauthorized.')->assertDontSee('Anders');
        auth('api')->forgetUser();
        $this->graphQL($query, ['id' => $current->team_round_id])->assertGraphQLErrorMessage('Unauthenticated.');
    }

    private function round(array $attributes = []): TeamRound
    {
        return TeamRound::factory()->create(array_merge([
            'clubhouse_id' => $this->clubhouse->id, 'user_id' => $this->user->id,
            'season_id' => 2026, 'round' => 12, 'game_date' => '2027-03-20',
        ], $attributes));
    }

    private function squad(TeamRound $round, ?Team $team = null): Squad
    {
        return Squad::query()->create(['team_round_id' => $round->id, 'team_id' => ($team ?? $this->team)->id, 'playerLimit' => 10]);
    }

    private function place(Squad $squad, array $attributes = []): void
    {
        $category = $squad->categories()->create(array_merge(['category' => 'HS', 'name' => '1. HS'], $attributes));
        $category->players()->create(['name' => 'Anders', 'gender' => 'M', 'member_ref_id' => '0101011111']);
    }

    private function check(Squad $squad, ?int $scenarioId = null): TestResponse
    {
        return $this->graphQL('mutation($id: ID!, $scenario: ID) {
            validateQualificationEligibility(teamRoundId: $id, scenarioId: $scenario) {
                squadIndex refId name status incompleteReasons uncountedRounds { teamRoundId name round }
            }
        }', ['id' => $squad->team_round_id, 'scenario' => $scenarioId])->assertGraphQLErrorFree();
    }
}
