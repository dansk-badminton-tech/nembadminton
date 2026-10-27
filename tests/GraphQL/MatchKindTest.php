<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\MatchKind;
use App\Enums\Permission;
use App\Enums\SeasonPhase;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use App\SeasonHistory\SeasonHistoryLoader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MatchKindTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private User $user;

    private TeamRound $round;

    protected function setUp(): void
    {
        parent::setUp();
        $clubhouse = Clubhouse::factory()->create();
        $this->user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->user->givePermissionTo([Permission::VIEW_TEAMROUNDS->value, Permission::EDIT_TEAMROUNDS->value]);
        $this->actingAs($this->user, 'api');
        $this->round = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id, 'user_id' => $this->user->id, 'round' => 12,
        ]);
    }

    #[Test]
    public function the_optional_choice_can_be_set_corrected_and_cleared(): void
    {
        $squad = $this->squad();
        foreach (['MEDAL_FINAL', 'QUALIFICATION', null] as $kind) {
            $this->updateKind($squad, $kind)->assertGraphQLErrorFree()->assertJsonPath('data.updateSquad.matchKind', $kind);
            $this->graphQL('query($id: ID!) { teamRound(id: $id) { squads { matchKind } } }', ['id' => $this->round->id])
                ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.squads.0.matchKind', $kind);
        }
        $this->updateKind($squad, 'UNSUPPORTED')->assertJsonStructure(['errors' => [['message']]]);
    }

    private function squad(): Squad
    {
        return Squad::query()->create(['team_round_id' => $this->round->id, 'playerLimit' => 8, 'tier' => 'Badmintonligaen']);
    }

    #[Test]
    public function match_kind_is_private_to_the_clubhouse_without_changing_public_lineup_sharing(): void
    {
        $squad = $this->squad();
        $this->updateKind($squad, 'MEDAL_FINAL')->assertGraphQLErrorFree();
        $other = Clubhouse::factory()->create();
        $this->actingAs(User::factory()->create(['clubhouse_id' => $other->id]), 'api');
        $this->updateKind($squad, 'QUALIFICATION')->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { squads { id matchKind } } }', ['id' => $this->round->id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.squads.0.matchKind', null)
            ->assertJsonPath('data.teamRound.squads.0.id', (string) $squad->id);
        $this->assertSame(MatchKind::MEDAL_FINAL, $squad->fresh()->match_kind);
    }

    #[Test]
    public function copying_retains_the_choice_but_still_resets_paper_status(): void
    {
        $team = Team::factory()->withCustomTier('Badmintonligaen')->create(['clubhouse_id' => $this->user->clubhouse_id]);
        $squad = $this->squad();
        $squad->update(['team_id' => $team->id, 'is_paper_squad' => true]);
        $this->updateKind($squad, 'MEDAL_FINAL')->assertGraphQLErrorFree();
        $this->graphQL('mutation($id: ID!) { copyTeamRound(id: $id) { squads { matchKind isPaperSquad } } }', ['id' => $this->round->id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.copyTeamRound.squads.0.matchKind', 'MEDAL_FINAL')
            ->assertJsonPath('data.copyTeamRound.squads.0.isPaperSquad', false);
    }

    #[Test]
    public function a_guest_cannot_read_match_kind_but_can_still_read_the_shared_lineup(): void
    {
        $squad = $this->squad();
        $this->updateKind($squad, 'MEDAL_FINAL')->assertGraphQLErrorFree();
        $this->app['auth']->forgetGuards();
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { squads { matchKind } } }', ['id' => $this->round->id])
            ->assertGraphQLErrorMessage('Unauthenticated.');
        $this->graphQL('query($id: ID!) { teamRound(id: $id) { squads { id } } }', ['id' => $this->round->id])
            ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.squads.0.id', (string) $squad->id);
    }

    #[Test]
    public function setting_match_kind_cannot_attach_a_foreign_team(): void
    {
        $squad = $this->squad();
        $foreign = Team::factory()->create();
        $response = $this->graphQL('mutation($input: UpdateSquadInput!) { updateSquad(input: $input) { id matchKind } }', [
            'input' => ['id' => (string) $squad->id, 'teamId' => (string) $foreign->id, 'matchKind' => 'MEDAL_FINAL'],
        ]);
        $response->assertJsonStructure(['errors' => [['message']]]);
        $this->assertNull($squad->fresh()->team_id);
        $this->assertNull($squad->fresh()->match_kind);
    }

    #[Test]
    public function each_squad_is_classified_independently_and_summary_never_guesses(): void
    {
        $liga = $this->squad();
        $lower = $this->squad();
        $lower->update(['tier' => '1. division']);
        $otherLiga = $this->squad();
        $this->updateKind($otherLiga, 'QUALIFICATION')->assertGraphQLErrorFree();
        foreach ([null => null, 'MEDAL_FINAL' => 'SLUTSPIL', 'QUALIFICATION' => 'KVALIFIKATION'] as $kind => $phase) {
            $this->updateKind($liga, $kind === '' ? null : $kind)->assertGraphQLErrorFree();
            $this->graphQL('query($id: ID!) { teamRound(id: $id) { seasonHistory { phase } } }', ['id' => $this->round->id])
                ->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.seasonHistory.0.phase', $phase);
            $history = app(SeasonHistoryLoader::class)->upTo($this->round);
            $this->assertSame(SeasonPhase::KVALIFIKATION, $history->rounds()[0]->squads[1]->phase);
            $this->assertSame(SeasonPhase::KVALIFIKATION, $history->rounds()[0]->squads[2]->phase);
        }
    }

    #[Test]
    public function context_changes_reuse_the_retained_choice_and_do_not_change_scenario_lineups(): void
    {
        $team = Team::factory()->withCustomTier('Badmintonligaen')->create(['clubhouse_id' => $this->user->clubhouse_id]);
        $squad = $this->squad();
        $squad->update(['team_id' => $team->id]);
        $official = $this->round->scenarios()->create(['name' => 'Plan A', 'is_official' => true]);
        $draft = $this->round->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);
        Member::query()->create(['refId' => '0101011111', 'name' => 'Anders', 'gender' => 'M', 'birthday' => '2001-01-01']);
        foreach ([$official, $draft] as $scenario) {
            $category = $squad->categories()->create(['category' => 'HS', 'name' => '1. HS', 'team_round_scenario_id' => $scenario->id]);
            $category->players()->create(['member_ref_id' => '0101011111', 'name' => $scenario->name, 'gender' => 'M']);
        }
        foreach (['MEDAL_FINAL', 'QUALIFICATION', null, 'MEDAL_FINAL'] as $kind) {
            $this->updateKind($squad, $kind)->assertGraphQLErrorFree();
            foreach ([$official, $draft] as $scenario) {
                $this->graphQL('query($id: ID!, $scenario: ID!) { teamRound(id: $id) { seasonHistory { phase } squads { matchKind categories(scenarioId: $scenario) { players { name } } } } }', [
                    'id' => $this->round->id, 'scenario' => (string) $scenario->id,
                ])->assertGraphQLErrorFree()->assertJsonPath('data.teamRound.squads.0.matchKind', $kind)
                    ->assertJsonPath('data.teamRound.seasonHistory.0.phase', match ($kind) {
                        'MEDAL_FINAL' => 'SLUTSPIL', 'QUALIFICATION' => 'KVALIFIKATION', null => null,
                    })
                    ->assertJsonPath('data.teamRound.squads.0.categories.0.players.0.name', $scenario->name);
            }
        }
        $phase = fn () => app(SeasonHistoryLoader::class)->upTo($this->round->fresh())->rounds()[0]->phase();
        $this->assertSame(SeasonPhase::SLUTSPIL, $phase());
        $this->changeRound(9);
        $this->assertSame(SeasonPhase::GRUNDSPIL, $phase());
        $this->changeRound(12);
        $this->changeSquad($squad, ['teamId' => null, 'tier' => '1. division']);
        $this->assertSame(SeasonPhase::KVALIFIKATION, $phase());
        $this->changeSquad($squad, ['teamId' => (string) $team->id, 'isPaperSquad' => true]);
        $this->assertSame(SeasonPhase::SLUTSPIL, $phase());
        $this->changeSquad($squad, ['isPaperSquad' => false]);
        $this->assertSame(SeasonPhase::SLUTSPIL, $phase());
        $this->assertSame('Badmintonligaen', $team->fresh()->tierName());
        $this->assertTrue($official->fresh()->is_official);
    }

    private function changeRound(int $round): void
    {
        $this->graphQL('mutation($input: UpdateTeamRoundInput!) { updateTeamRound(input: $input) { id round } }', [
            'input' => ['id' => $this->round->id, 'round' => $round, 'version' => (string) $this->round->version,
                'gameDate' => (string) $this->round->game_date],
        ])->assertGraphQLErrorFree()->assertJsonPath('data.updateTeamRound.round', $round);
    }

    /** @param array<string, mixed> $input */
    private function changeSquad(Squad $squad, array $input): void
    {
        $this->graphQL('mutation($input: UpdateSquadInput!) { updateSquad(input: $input) { id matchKind } }', [
            'input' => ['id' => (string) $squad->id, ...$input],
        ])->assertGraphQLErrorFree()->assertJsonPath('data.updateSquad.matchKind', 'MEDAL_FINAL');
    }

    private function updateKind(Squad $squad, ?string $kind): TestResponse
    {
        return $this->graphQL('mutation($input: UpdateSquadInput!) { updateSquad(input: $input) { id matchKind } }', [
            'input' => ['id' => (string) $squad->id, 'matchKind' => $kind],
        ]);
    }
}
