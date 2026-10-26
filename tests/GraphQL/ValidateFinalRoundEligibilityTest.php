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

class ValidateFinalRoundEligibilityTest extends TestCase
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
    public function no_earlier_appearance_warns_once_per_member_on_the_current_squad(): void
    {
        $squad = $this->squad($this->round());
        $this->place($squad);
        $this->place($squad, ['category' => 'HD', 'name' => '1. HD']);
        $this->check($squad)->assertJsonPath('data.validateFinalRoundEligibility', [[
            'squadIndex' => 0, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'REQUIREMENT_NOT_MET',
        ]]);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'REQUIREMENT_NOT_MET', 'incompleteReasons' => [], 'uncountedRounds' => [],
        ]);
    }

    private function round(array $attributes = []): TeamRound
    {
        return TeamRound::factory()->create(array_merge([
            'clubhouse_id' => $this->clubhouse->id, 'user_id' => $this->user->id,
            'season_id' => 2026, 'round' => 11, 'game_date' => '2027-03-20',
        ], $attributes));
    }

    #[Test]
    public function applicability_uses_each_squads_original_dh_tier_and_round_not_its_match_format(): void
    {
        foreach ([
            ['Badmintonligaen', [10 => 'NOT_APPLICABLE', 11 => 'NOT_APPLICABLE', 12 => 'REQUIREMENT_NOT_MET']],
            ['1. division', [10 => 'NOT_APPLICABLE', 11 => 'REQUIREMENT_NOT_MET', 12 => 'REQUIREMENT_NOT_MET']],
            ['2. division', [10 => 'NOT_APPLICABLE', 11 => 'REQUIREMENT_NOT_MET', 12 => 'REQUIREMENT_NOT_MET']],
            ['3. division', [10 => 'NOT_APPLICABLE', 11 => 'REQUIREMENT_NOT_MET', 12 => 'REQUIREMENT_NOT_MET']],
            ['Danmarksserien', [10 => 'NOT_APPLICABLE', 11 => 'REQUIREMENT_NOT_MET', 12 => 'REQUIREMENT_NOT_MET']],
        ] as [$tier, $rounds]) {
            $team = Team::factory()->withCustomTier($tier)->create(['clubhouse_id' => $this->clubhouse->id]);
            foreach ($rounds as $number => $expected) {
                $squad = $this->squad($this->round(['round' => $number]), $team);
                $this->place($squad);
                $squad->update(['tier' => 'Serie 1']);
                $this->check($squad)->assertJsonPath('data.validateFinalRoundEligibility.0.status', $expected);
                $squad->update(['is_paper_squad' => true]);
                $this->check($squad)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'NOT_APPLICABLE');
            }
        }
    }

    private function squad(TeamRound $round, ?Team $team = null): Squad
    {
        return Squad::query()->create(['team_round_id' => $round->id, 'team_id' => ($team ?? $this->team)->id, 'playerLimit' => 10]);
    }

    #[Test]
    public function one_same_or_lower_team_appearance_proves_the_requirement_using_historical_squad_order(): void
    {
        foreach (['Badmintonligaen', '1. division', '2. division', '3. division', 'Danmarksserien'] as $tier) {
            $team = Team::factory()->withCustomTier($tier)->create(['clubhouse_id' => $this->clubhouse->id]);
            foreach ($tier === 'Badmintonligaen' ? [12] : [11, 12] as $number) {
                $current = $this->squad($this->round(['round' => $number]), $team);
                $this->place($current);
                $earlier = $this->round(['round' => 1, 'game_date' => '2026-09-20']);
                $higher = $this->squad($earlier);
                $same = $this->squad($earlier, $team);
                // The nominally stronger tier is nevertheless lower in this historical Squad order.
                $lower = $this->squad($earlier, Team::factory()->withCustomTier('Badmintonligaen')->create(['clubhouse_id' => $this->clubhouse->id]));
                $category = $this->place($higher);
                $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'REQUIREMENT_NOT_MET');
                $category->update(['squad_id' => $same->id]);
                $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'SATISFIED');
                $category->update(['squad_id' => $lower->id]);
                $lower->update(['is_paper_squad' => true]);
                $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'SATISFIED');
                $earlier->delete();
            }
        }
    }

    private function place(Squad $squad, array $attributes = []): SquadCategory
    {
        $category = $squad->categories()->create(array_merge(['category' => 'HS', 'name' => '1. HS'], $attributes));
        $category->players()->create(['name' => 'Anders', 'gender' => 'M', 'member_ref_id' => '0101011111']);

        return $category;
    }

    #[Test]
    public function incomplete_current_context_is_not_success_or_proven_ineligibility(): void
    {
        foreach (['round' => 'MISSING_ROUND', 'season_id' => 'MISSING_SEASON', 'game_date' => 'MISSING_GAME_DATE'] as $field => $reason) {
            $squad = $this->squad($this->round([$field => null]));
            $this->place($squad);
            $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
                'status' => 'CANNOT_VERIFY', 'incompleteReasons' => [$reason], 'uncountedRounds' => [],
            ]);
        }
        $squad = $this->squad($this->round());
        $this->place($squad);
        $squad->update(['team_id' => null, 'tier' => '2. division']);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'CANNOT_VERIFY', 'incompleteReasons' => ['MISSING_TEAM'], 'uncountedRounds' => [],
        ]);
        $squad->update(['tier' => 'Ukendt række']);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'CANNOT_VERIFY', 'incompleteReasons' => ['UNKNOWN_TIER', 'MISSING_TEAM'], 'uncountedRounds' => [],
        ]);
        $squad->update(['team_id' => Team::factory()->withCustomTier('Ukendt række')->create(['clubhouse_id' => $this->clubhouse->id])->id]);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'CANNOT_VERIFY', 'incompleteReasons' => ['UNKNOWN_TIER'], 'uncountedRounds' => [],
        ]);
        $squad->update(['is_paper_squad' => true]);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'NOT_APPLICABLE', 'incompleteReasons' => [], 'uncountedRounds' => [],
        ]);
    }

    #[Test]
    public function non_applicable_squads_do_not_report_missing_comparison_context(): void
    {
        $round = $this->round(['round' => 10, 'season_id' => null, 'game_date' => null]);
        $squad = $this->squad($round);
        $this->place($squad);
        $squad->update(['team_id' => null, 'tier' => '2. division']);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'NOT_APPLICABLE', 'incompleteReasons' => [], 'uncountedRounds' => [],
        ]);

        $round->update(['round' => null]);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'CANNOT_VERIFY',
            'incompleteReasons' => ['MISSING_ROUND', 'MISSING_TEAM', 'MISSING_SEASON', 'MISSING_GAME_DATE'],
            'uncountedRounds' => [],
        ]);

        $unknownTeam = Team::factory()->withCustomTier('Ukendt række')->create(['clubhouse_id' => $this->clubhouse->id]);
        $squad->update(['is_paper_squad' => true, 'team_id' => $unknownTeam->id]);
        $this->diagnostics($squad)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'NOT_APPLICABLE', 'incompleteReasons' => [], 'uncountedRounds' => [],
        ]);
    }

    #[Test]
    public function uncomparable_history_is_inconclusive_unless_another_appearance_proves_the_requirement(): void
    {
        $current = $this->squad($this->round());
        $this->place($current);
        $earlier = $this->squad($this->round(['game_date' => '2026-09-20']));
        $this->place($earlier);
        $earlier->update(['team_id' => null]);
        $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'CANNOT_VERIFY');
        $this->place($this->squad($this->round(['game_date' => '2026-09-27'])));
        $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'SATISFIED');
    }

    #[Test]
    public function uncertainty_identifies_each_incomparable_historical_round_once_and_proof_removes_it(): void
    {
        $current = $this->squad($this->round());
        $this->place($current);
        $missing = $this->round(['name' => 'Efterår uden holdlink', 'round' => 2, 'game_date' => '2026-09-20']);
        $unknownPhase = $this->round(['name' => 'Ekstra holdrunde', 'round' => null, 'game_date' => '2026-09-27']);
        foreach ([$missing, $unknownPhase] as $round) {
            $squad = $this->squad($round);
            $squad->update(['team_id' => null]);
            $this->place($squad);
            $this->place($squad, ['category' => 'HD', 'name' => '1. HD']);
        }
        $foreign = $this->squad($this->round([
            'name' => 'Other Clubhouse secret', 'game_date' => '2026-09-20', 'clubhouse_id' => Clubhouse::factory()->create()->id,
        ]));
        $foreign->update(['team_id' => null]);
        $this->place($foreign);
        $this->diagnostics($current)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'CANNOT_VERIFY',
            'incompleteReasons' => ['INCOMPARABLE_HISTORY'],
            'uncountedRounds' => [
                ['teamRoundId' => $missing->id, 'name' => 'Efterår uden holdlink', 'round' => 2],
                ['teamRoundId' => $unknownPhase->id, 'name' => 'Ekstra holdrunde', 'round' => null],
            ],
        ]);

        $this->place($this->squad($this->round(['round' => null, 'game_date' => '2026-10-04'])));
        $this->diagnostics($current)->assertJsonPath('data.validateFinalRoundEligibility.0', [
            'status' => 'SATISFIED', 'incompleteReasons' => [], 'uncountedRounds' => [],
        ]);
    }

    private function diagnostics(Squad $squad): TestResponse
    {
        return $this->graphQL('mutation($id: ID!) {
            validateFinalRoundEligibility(teamRoundId: $id) {
                status incompleteReasons uncountedRounds { teamRoundId name round }
            }
        }', ['id' => $squad->team_round_id])->assertGraphQLErrorFree();
    }

    #[Test]
    public function only_earlier_official_lineups_in_this_season_and_clubhouse_count(): void
    {
        Season::query()->firstOrCreate(['id' => 2025], ['season_name' => '2025/2026']);
        $current = $this->squad($this->round());
        $this->place($current);
        $this->place($this->squad($this->round(['game_date' => '2027-03-21'])));
        $this->place($this->squad($this->round(['game_date' => '2027-03-20'])));
        $this->place($this->squad($this->round(['game_date' => '2026-09-20', 'season_id' => 2025])));
        $this->place($this->squad($this->round(['game_date' => '2026-09-20', 'clubhouse_id' => Clubhouse::factory()->create()->id])));
        $earlier = $this->round(['game_date' => '2026-09-27']);
        $draft = $earlier->scenarios()->create(['name' => 'Kladde', 'is_official' => false]);
        $this->place($this->squad($earlier), ['team_round_scenario_id' => $draft->id]);
        $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'REQUIREMENT_NOT_MET');
        $draft->update(['is_official' => true]);
        $this->check($current)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'SATISFIED');
    }

    #[Test]
    public function mixed_tiers_and_current_scenario_placements_are_checked_independently(): void
    {
        $round = $this->round();
        $liga = $this->squad($round, Team::factory()->withCustomTier('Badmintonligaen')->create(['clubhouse_id' => $this->clubhouse->id]));
        $division = $this->squad($round);
        $this->place($liga);
        $this->place($division);
        $draft = $round->scenarios()->create(['name' => 'Liga only', 'is_official' => false]);
        $this->place($liga, ['team_round_scenario_id' => $draft->id]);
        $this->check($division)->assertJsonPath('data.validateFinalRoundEligibility', [
            ['squadIndex' => 0, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'NOT_APPLICABLE'],
            ['squadIndex' => 1, 'refId' => '0101011111', 'name' => 'Anders', 'status' => 'REQUIREMENT_NOT_MET'],
        ]);
        $this->check($division, $draft->id)->assertJsonCount(1, 'data.validateFinalRoundEligibility');
        $round->update(['round' => 12]);
        $this->check($division, $draft->id)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'REQUIREMENT_NOT_MET');
    }

    #[Test]
    public function omitted_scenario_uses_the_saved_official_lineup_and_an_empty_draft_stays_empty(): void
    {
        $round = $this->round();
        $squad = $this->squad($round);
        $legacy = $this->place($squad);
        $this->check($squad)->assertJsonCount(1, 'data.validateFinalRoundEligibility');
        $official = $round->scenarios()->create(['name' => 'Officiel', 'is_official' => true]);
        $category = $this->place($squad, ['team_round_scenario_id' => $official->id]);
        $category->players()->update(['name' => 'Saved official name']);
        $draft = $round->scenarios()->create(['name' => 'Empty draft', 'is_official' => false]);

        $this->check($squad)->assertJsonCount(1, 'data.validateFinalRoundEligibility')
            ->assertJsonPath('data.validateFinalRoundEligibility.0.name', 'Saved official name');
        $this->check($squad, $official->id)->assertJsonCount(1, 'data.validateFinalRoundEligibility');
        $this->check($squad, $draft->id)->assertJsonPath('data.validateFinalRoundEligibility', []);
        $official->update(['is_official' => false]);
        $draft->update(['is_official' => true]);
        // Match the editor's legacy-category fallback for Squads without official Scenario categories.
        $this->check($squad)->assertJsonPath('data.validateFinalRoundEligibility.0.name', 'Anders');
        $legacy->players()->delete();
        $this->check($squad)->assertJsonPath('data.validateFinalRoundEligibility', []);
    }

    #[Test]
    public function scenarios_from_another_round_or_clubhouse_and_missing_scenarios_are_rejected(): void
    {
        $current = $this->squad($this->round());
        foreach ([$this->round(), $this->round(['clubhouse_id' => Clubhouse::factory()->create()->id])] as $round) {
            $scenario = $round->scenarios()->create(['name' => 'Private draft', 'is_official' => false]);
            $this->place($this->squad($round), ['team_round_scenario_id' => $scenario->id]);
            $this->graphQL('mutation($id: ID!, $scenario: ID) {
                validateFinalRoundEligibility(teamRoundId: $id, scenarioId: $scenario) { refId name }
            }', ['id' => $current->team_round_id, 'scenario' => $scenario->id])
                ->assertGraphQLErrorMessage('This action is unauthorized.')
                ->assertJsonPath('data', null)->assertDontSee('Anders');
        }
        $this->graphQL('mutation($id: ID!, $scenario: ID) {
            validateFinalRoundEligibility(teamRoundId: $id, scenarioId: $scenario) { refId }
        }', ['id' => $current->team_round_id, 'scenario' => 999999])
            ->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    #[Test]
    public function another_clubhouse_and_public_sharing_cannot_access_validation(): void
    {
        $current = $this->squad($this->round());
        $this->place($current);
        $historical = $this->round(['name' => 'Confidential historical lineup', 'round' => 1, 'game_date' => '2026-09-20']);
        $unlinked = $this->squad($historical);
        $unlinked->update(['team_id' => null]);
        $this->place($unlinked);
        $this->diagnostics($current)->assertJsonPath('data.validateFinalRoundEligibility.0.uncountedRounds.0.teamRoundId', $historical->id);
        $query = 'mutation($id: ID!) {
            validateFinalRoundEligibility(teamRoundId: $id) {
                status incompleteReasons uncountedRounds { teamRoundId name round }
            }
        }';
        $variables = ['id' => $current->team_round_id];
        $foreignUser = User::factory()->create(['clubhouse_id' => Clubhouse::factory()->create()->id]);
        setPermissionsTeamId($foreignUser->clubhouse_id);
        $foreignUser->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($foreignUser, 'api');
        $this->graphQL($query, $variables)->assertGraphQLErrorMessage('This action is unauthorized.')
            ->assertDontSee($historical->id)->assertDontSee('Confidential historical lineup');
        auth('api')->forgetUser();
        $this->graphQL($query, $variables)->assertGraphQLErrorMessage('Unauthenticated.')
            ->assertDontSee($historical->id)->assertDontSee('Confidential historical lineup');
        // The intentional public TeamRound link remains usable, but cannot expose earlier rounds.
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { id } }', ['id' => $current->team_round_id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.id', $current->team_round_id);
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { seasonHistory { id name } } }', ['id' => $current->team_round_id])
            ->assertGraphQLErrorMessage('Unauthenticated.')
            ->assertJsonPath('data.teamRound.seasonHistory', null)
            ->assertDontSee($historical->id)->assertDontSee('Confidential historical lineup');
    }

    #[Test]
    public function persisted_corrections_refresh_each_scenarios_placements_and_shared_applicability(): void
    {
        $this->user->givePermissionTo([Permission::EDIT_TEAMROUNDS->value, Permission::EDIT_TEAMS->value]);
        $round = $this->round(['round' => 10]);
        $ligaTeam = Team::factory()->withCustomTier('Badmintonligaen')->create(['clubhouse_id' => $this->clubhouse->id]);
        $liga = $this->squad($round, $ligaTeam);
        $division = $this->squad($round);
        $this->place($liga);
        $this->place($division);
        $draft = $round->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);
        // Unlike the Official Lineup, this Scenario only places Anders on the division Squad, twice.
        $this->place($division, ['team_round_scenario_id' => $draft->id]);
        $this->place($division, ['team_round_scenario_id' => $draft->id, 'category' => 'HD']);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['NOT_APPLICABLE', 'NOT_APPLICABLE']);
        $this->correctRound($round, ['round' => 11]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['NOT_APPLICABLE', 'REQUIREMENT_NOT_MET']);
        $this->persistedCheck($round, $draft->id)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['REQUIREMENT_NOT_MET'])
            ->assertJsonPath('data.validateFinalRoundEligibility.0.squadIndex', 1);
        $this->correctSquad($division, ['isPaperSquad' => true]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['NOT_APPLICABLE', 'NOT_APPLICABLE']);
        $this->persistedCheck($round, $draft->id)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['NOT_APPLICABLE']);
        $this->correctSquad($division, ['isPaperSquad' => false]);
        $this->correctRound($round, ['round' => 12]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['REQUIREMENT_NOT_MET', 'REQUIREMENT_NOT_MET']);
        $this->correctRound($round, ['round' => 11]);
        $this->graphQL('mutation($input: UpdateTeamInput!) { updateTeam(input: $input) { customTierName } }', [
            'input' => ['id' => $this->team->id, 'customTierName' => 'Badmintonligaen'],
        ])->assertGraphQLErrorFree()->assertJsonPath('data.updateTeam.customTierName', 'Badmintonligaen');
        $this->persistedCheck($round, $draft->id)->assertJsonPath('data.validateFinalRoundEligibility.*.status', ['NOT_APPLICABLE']);
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { scenarios { id isOfficial } squads { team { customTierName } categories { players { refId } } } } }', ['id' => $round->id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.scenarios.0.isOfficial', false)
            ->assertJsonPath('data.teamRound.squads.0.team.customTierName', 'Badmintonligaen')
            ->assertJsonCount(1, 'data.teamRound.squads.0.categories.0.players')
            ->assertJsonCount(1, 'data.teamRound.squads.1.categories.0.players');
    }

    private function correctRound(TeamRound $round, array $corrections): TestResponse
    {
        return $this->graphQL('mutation($input: UpdateTeamRoundInput!) { updateTeamRound(input: $input) { id round } }', [
            'input' => array_merge(['id' => $round->id, 'version' => '2026-03-01', 'gameDate' => '2027-03-20'], $corrections),
        ])->assertGraphQLErrorFree();
    }

    #[Test]
    public function completing_current_context_and_historical_team_links_replaces_uncertainty_on_revalidation(): void
    {
        $this->user->givePermissionTo([Permission::EDIT_TEAMROUNDS->value, Permission::EDIT_TEAMS->value]);
        $round = $this->round(['round' => null, 'season_id' => null, 'game_date' => null]);
        $current = $this->squad($round);
        $current->update(['team_id' => null, 'tier' => 'Ukendt']);
        $this->place($current);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'CANNOT_VERIFY');
        $this->correctRound($round, ['round' => 11, 'seasonId' => 2026]);
        $this->correctSquad($current, ['teamId' => $this->team->id]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validateFinalRoundEligibility.0.incompleteReasons', []);

        $historicalRound = $this->round(['game_date' => '2026-09-20', 'round' => 2]);
        $higherTeam = Team::factory()->withCustomTier('1. division')->create(['clubhouse_id' => $this->clubhouse->id]);
        $higher = $this->squad($historicalRound, $higherTeam);
        $this->place($higher);
        $target = $this->squad($historicalRound);
        $this->correctSquad($target, ['teamId' => null]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'CANNOT_VERIFY')
            ->assertJsonPath('data.validateFinalRoundEligibility.0.uncountedRounds', [['teamRoundId' => $historicalRound->id]]);
        $this->correctSquad($target, ['teamId' => $this->team->id]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'REQUIREMENT_NOT_MET')
            ->assertJsonPath('data.validateFinalRoundEligibility.0.uncountedRounds', []);
        $this->correctSquad($target, ['teamId' => null]);
        $this->correctSquad($higher, ['teamId' => $this->team->id]);
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'SATISFIED')
            ->assertJsonPath('data.validateFinalRoundEligibility.0.incompleteReasons', [])
            ->assertJsonPath('data.validateFinalRoundEligibility.0.uncountedRounds', []);
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { squads { team { id customTierName } categories { players { refId } } } scenarios { id isOfficial } } }', ['id' => $round->id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.squads.0.team.customTierName', '2. division')
            ->assertJsonPath('data.teamRound.squads.0.categories.0.players.0.refId', '0101011111')
            ->assertJsonPath('data.teamRound.scenarios', []);
    }

    private function correctSquad(Squad $squad, array $corrections): TestResponse
    {
        return $this->graphQL('mutation($input: UpdateSquadInput!) { updateSquad(input: $input) { id team { id } isPaperSquad } }', [
            'input' => array_merge(['id' => $squad->id], $corrections),
        ])->assertGraphQLErrorFree();
    }

    #[Test]
    public function correction_targets_and_new_team_links_remain_isolated_to_the_clubhouse(): void
    {
        $this->user->givePermissionTo([Permission::EDIT_TEAMROUNDS->value, Permission::EDIT_TEAMS->value]);
        $round = $this->round();
        $current = $this->squad($round);
        $this->place($current);
        $otherClubhouse = Clubhouse::factory()->create();
        $foreignRound = $this->round(['clubhouse_id' => $otherClubhouse->id]);
        $foreignTeam = Team::factory()->withCustomTier('Badmintonligaen')->create(['clubhouse_id' => $otherClubhouse->id]);
        $foreignSquad = $this->squad($foreignRound, $foreignTeam);
        $this->graphQL('mutation($input: UpdateSquadInput!) { updateSquad(input: $input) { id } }', [
            'input' => ['id' => $foreignSquad->id, 'teamId' => $this->team->id],
        ])->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->graphQL('mutation($input: UpdateSquadInput!) { updateSquad(input: $input) { id } }', [
            'input' => ['id' => $current->id, 'teamId' => $foreignTeam->id],
        ])->assertGraphQLValidationError('input.teamId', 'Vælg et hold fra dit eget klubhus.');
        $this->graphQL('mutation($input: UpdateTeamRoundInput!) { updateTeamRound(input: $input) { id } }', [
            'input' => ['id' => $foreignRound->id, 'round' => 10, 'version' => '2026-03-01', 'gameDate' => '2027-03-20'],
        ])->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->graphQL('mutation($input: UpdateTeamInput!) { updateTeam(input: $input) { id } }', [
            'input' => ['id' => $foreignTeam->id, 'customTierName' => '2. division'],
        ])->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->persistedCheck($round)->assertJsonPath('data.validateFinalRoundEligibility.0.status', 'REQUIREMENT_NOT_MET');
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { squads { team { id } } } }', ['id' => $round->id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.squads.0.team.id', (string) $this->team->id);
    }

    private function persistedCheck(TeamRound $round, ?int $scenarioId = null): TestResponse
    {
        return $this->graphQL('mutation($id: ID!, $scenario: ID) {
            validateFinalRoundEligibility(teamRoundId: $id, scenarioId: $scenario) { squadIndex refId status incompleteReasons uncountedRounds { teamRoundId } }
        }', ['id' => $round->id, 'scenario' => $scenarioId])->assertGraphQLErrorFree();
    }

    private function check(Squad $squad, ?int $scenarioId = null): TestResponse
    {
        return $this->graphQL('mutation($id: ID!, $scenario: ID) {
            validateFinalRoundEligibility(teamRoundId: $id, scenarioId: $scenario) { squadIndex refId name status }
        }', ['id' => $squad->team_round_id, 'scenario' => $scenarioId])->assertGraphQLErrorFree();
    }
}
