<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Mail\TeamMail;
use App\Models\Clubhouse;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A Paper Squad (papirhold) is a Squad with a Team that the coach marks as a paper squad.
 */
class PaperSquadTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Clubhouse $clubhouse;

    private User $user;

    private TeamRound $teamRound;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = User::factory()->create(['clubhouse_id' => $this->clubhouse->id]);
        setPermissionsTeamId($this->clubhouse->id);
        $this->user->givePermissionTo([
            Permission::VIEW_TEAMROUNDS->value,
            Permission::EDIT_TEAMROUNDS->value,
        ]);
        $this->actingAs($this->user, 'api');
        $this->teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $this->clubhouse->id,
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function a_squad_is_not_a_paper_squad_by_default(): void
    {
        $this->createSquad(['teamId' => (string) $this->team()->id])
            ->assertGraphQLErrorFree()
            ->assertJsonPath('data.createSquad.isPaperSquad', false);
    }

    #[Test]
    public function a_squad_with_a_team_can_be_created_as_a_paper_squad(): void
    {
        $team = $this->team();

        $this->createSquad(['teamId' => (string) $team->id, 'isPaperSquad' => true])
            ->assertGraphQLErrorFree()
            ->assertJsonPath('data.createSquad.isPaperSquad', true);

        $this->assertDatabaseHas('squads', ['team_id' => $team->id, 'is_paper_squad' => true]);
    }

    #[Test]
    public function a_squad_without_a_team_cannot_be_created_as_a_paper_squad(): void
    {
        $this->createSquad(['name' => 'Engangshold', 'isPaperSquad' => true])
            ->assertGraphQLErrorMessage('Kun en holdopstilling med et hold kan være papirhold.');

        $this->assertDatabaseCount('squads', 0);
    }

    #[Test]
    public function the_paper_squad_flag_can_be_switched_on_and_off_on_an_existing_squad(): void
    {
        $squad = $this->squad(['team_id' => $this->team()->id]);

        $this->updateSquad(['id' => (string) $squad->id, 'isPaperSquad' => true])
            ->assertGraphQLErrorFree()
            ->assertJsonPath('data.updateSquad.isPaperSquad', true);
        $this->assertTrue($squad->fresh()->is_paper_squad);

        $this->updateSquad(['id' => (string) $squad->id, 'isPaperSquad' => false])
            ->assertGraphQLErrorFree()
            ->assertJsonPath('data.updateSquad.isPaperSquad', false);
        $this->assertFalse($squad->fresh()->is_paper_squad);
    }

    #[Test]
    public function a_squad_without_a_team_cannot_be_marked_as_a_paper_squad(): void
    {
        $squad = $this->squad(['name' => 'Engangshold']);

        $this->updateSquad(['id' => (string) $squad->id, 'isPaperSquad' => true])
            ->assertGraphQLErrorMessage('Kun en holdopstilling med et hold kan være papirhold.');

        $this->assertFalse($squad->fresh()->is_paper_squad);
    }

    #[Test]
    public function the_team_of_a_paper_squad_cannot_be_disconnected_while_it_stays_a_paper_squad(): void
    {
        $squad = $this->squad(['team_id' => $this->team()->id, 'is_paper_squad' => true]);

        $this->updateSquad(['id' => (string) $squad->id, 'teamId' => null])
            ->assertGraphQLErrorMessage('Kun en holdopstilling med et hold kan være papirhold.');

        $this->updateSquad(['id' => (string) $squad->id, 'teamId' => null, 'isPaperSquad' => false])
            ->assertGraphQLErrorFree();
        $this->assertDatabaseHas('squads', ['id' => $squad->id, 'team_id' => null, 'is_paper_squad' => false]);
    }

    #[Test]
    public function it_denies_marking_a_squad_in_another_clubhouse_as_a_paper_squad(): void
    {
        $otherClubhouse = Clubhouse::factory()->create();
        $otherTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $otherClubhouse->id,
            'user_id' => User::factory()->create(['clubhouse_id' => $otherClubhouse->id])->id,
        ]);
        $otherTeam = Team::factory()->create(['clubhouse_id' => $otherClubhouse->id]);
        $otherSquad = $this->squad(['team_id' => $otherTeam->id], $otherTeamRound);

        $this->updateSquad(['id' => (string) $otherSquad->id, 'isPaperSquad' => true])
            ->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->assertFalse($otherSquad->fresh()->is_paper_squad);
    }

    #[Test]
    public function it_denies_creating_a_paper_squad_in_another_clubhouses_team_round(): void
    {
        $otherClubhouse = Clubhouse::factory()->create();
        $otherTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $otherClubhouse->id,
            'user_id' => User::factory()->create(['clubhouse_id' => $otherClubhouse->id])->id,
        ]);
        $otherTeam = Team::factory()->create(['clubhouse_id' => $otherClubhouse->id]);

        $this->createSquad(['teamId' => (string) $otherTeam->id, 'isPaperSquad' => true], $otherTeamRound)
            ->assertGraphQLErrorMessage('This action is unauthorized.');

        $this->assertDatabaseCount('squads', 0);
    }

    #[Test]
    public function the_shared_view_shows_whether_a_squad_is_a_paper_squad(): void
    {
        $team = $this->team();
        $this->squad(['team_id' => $team->id, 'is_paper_squad' => true]);
        $this->squad(['name' => 'Engangshold']);

        $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                teamRound(id: $id) { squads { isPaperSquad } }
            }
        ', ['id' => $this->teamRound->id])
            ->assertGraphQLErrorFree()
            ->assertJsonPath('data.teamRound.squads', [['isPaperSquad' => true], ['isPaperSquad' => false]]);
    }

    #[Test]
    public function the_team_round_mail_names_its_paper_squads(): void
    {
        $this->squad(['name' => 'Engangshold']);
        $this->squad(['team_id' => Team::factory()->create(['clubhouse_id' => $this->clubhouse->id, 'name' => 'Højbjerg 2'])->id, 'is_paper_squad' => true]);

        (new TeamMail($this->teamRound, null))->assertSeeInText('Papirhold: Hold 2 (Højbjerg 2)');
    }

    #[Test]
    public function the_team_round_mail_has_no_paper_squad_line_without_paper_squads(): void
    {
        $this->squad(['team_id' => $this->team()->id]);

        (new TeamMail($this->teamRound, null))->assertDontSeeInText('Papirhold');
    }

    #[Test]
    public function a_copied_team_round_starts_without_paper_squads(): void
    {
        $this->squad(['team_id' => $this->team()->id, 'is_paper_squad' => true]);

        $copyId = $this->graphQL(/** @lang GraphQL */ '
            mutation($id: ID!) { copyTeamRound(id: $id) { id } }
        ', ['id' => $this->teamRound->id])
            ->assertGraphQLErrorFree()
            ->json('data.copyTeamRound.id');

        $this->assertFalse(Squad::query()->where('team_round_id', $copyId)->sole()->is_paper_squad);
    }

    private function team(): Team
    {
        return Team::factory()->create(['clubhouse_id' => $this->clubhouse->id]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function squad(array $attributes, ?TeamRound $teamRound = null): Squad
    {
        return Squad::query()->create([
            'team_round_id' => ($teamRound ?? $this->teamRound)->id,
            'playerLimit' => 8,
            ...$attributes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function createSquad(array $input, ?TeamRound $teamRound = null): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($input: CreateSquadInput!) {
                createSquad(input: $input) { id isPaperSquad }
            }
        ', ['input' => [
            'teamRound' => ['connect' => ($teamRound ?? $this->teamRound)->id],
            'playerLimit' => 8,
            'categories' => ['create' => [['category' => 'HS', 'name' => '1. HS']]],
            ...$input,
        ]]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private function updateSquad(array $input): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateSquadInput!) {
                updateSquad(input: $input) { id isPaperSquad }
            }
        ', ['input' => $input]);
    }
}
