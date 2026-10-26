<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Point;
use App\Models\Squad;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * §38 stk. 4: the TeamRound's Ranking List decides which Squad a player may stand on, also for a Squad with its own.
 * The server reads the lineup and the Members' points on that Ranking List itself. A coach's correction of a Squad's
 * points only counts for the order on the Squad (stk. 2-3).
 */
class ValidateTeamRoundCrossSquadsTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    private const TEAM_ROUND_RANKING = '2026-02-01';

    private const OWN_RANKING = '2026-03-01';

    private const ANDERS = '0101901111';

    private const BO = '0202902222';

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Clubhouse $clubhouse;

    private TeamRound $teamRound;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clubhouse = Clubhouse::factory()->create();
        $user = $this->userOf($this->clubhouse);
        $this->actingAs($user, 'api');
        $this->teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $this->clubhouse->id,
            'user_id' => $user->id,
            'version' => self::TEAM_ROUND_RANKING,
        ]);
        $this->member(self::ANDERS, 'Anders');
        $this->member(self::BO, 'Bo');
        // Anders stands on Hold 1 with 2000 HS on the TeamRound's Ranking List in every test.
        $this->rankingPoints(self::ANDERS, self::TEAM_ROUND_RANKING, 2000);
    }

    #[Test]
    public function a_squad_with_its_own_ranking_is_legal_on_the_team_rounds_though_over_the_limit_on_its_own(): void
    {
        $this->squad(1, null, self::ANDERS, 'Anders', self::TEAM_ROUND_RANKING, 2000);
        $this->squad(2, self::OWN_RANKING, self::BO, 'Bo', self::OWN_RANKING, 2060);
        $this->rankingPoints(self::BO, self::TEAM_ROUND_RANKING, 1940);

        $this->assertSame([], $this->validate());
    }

    #[Test]
    public function a_squad_with_its_own_ranking_is_over_the_limit_on_the_team_rounds_though_legal_on_its_own(): void
    {
        $this->squad(1, null, self::ANDERS, 'Anders', self::TEAM_ROUND_RANKING, 2000);
        $this->squad(2, self::OWN_RANKING, self::BO, 'Bo', self::OWN_RANKING, 1940);
        $this->rankingPoints(self::BO, self::TEAM_ROUND_RANKING, 2060);

        $this->assertSame([[
            'refId' => self::BO,
            'belowPlayer' => [['refId' => self::ANDERS, 'category' => 'HS', 'balance' => 60]],
        ]], $this->validate());
    }

    #[Test]
    public function a_coachs_correction_does_not_count_across_squads(): void
    {
        $this->squad(1, null, self::ANDERS, 'Anders', self::TEAM_ROUND_RANKING, 2000);
        // On the Ranking List Bo has 1940, which is legal; the coach corrected him to 2060 on the Squad. The correction
        // only counts for the order on the Squad.
        $this->squad(2, null, self::BO, 'Bo', self::TEAM_ROUND_RANKING, 2060);
        $this->rankingPoints(self::BO, self::TEAM_ROUND_RANKING, 1940);

        $this->assertSame([], $this->validate());
    }

    #[Test]
    public function a_player_with_only_points_added_by_the_coach_has_nothing_to_compare(): void
    {
        $this->squad(1, null, self::ANDERS, 'Anders', self::TEAM_ROUND_RANKING, 2000);
        // Bo isn't on the Ranking List; the coach added 2060 on the Squad. Like an unranked player, he is allowed.
        $this->squad(2, null, self::BO, 'Bo', self::TEAM_ROUND_RANKING, 2060);

        $this->assertSame([], $this->validate());
    }

    #[Test]
    public function a_team_round_without_a_ranking_list_compares_the_squads_stored_points(): void
    {
        // Only a new TeamRound can be without one; changing it to none isn't possible.
        TeamRound::query()->whereKey($this->teamRound->id)->update(['version' => null]);
        $this->squad(1, null, self::ANDERS, 'Anders', self::TEAM_ROUND_RANKING, 2000);
        $this->squad(2, null, self::BO, 'Bo', self::TEAM_ROUND_RANKING, 2060);
        $this->rankingPoints(self::BO, self::TEAM_ROUND_RANKING, 1940);

        $this->assertSame([[
            'refId' => self::BO,
            'belowPlayer' => [['refId' => self::ANDERS, 'category' => 'HS', 'balance' => 60]],
        ]], $this->validate());
    }

    #[Test]
    public function it_denies_validating_a_team_round_in_another_clubhouse(): void
    {
        $this->squad(1, null, self::ANDERS, 'Anders', self::TEAM_ROUND_RANKING, 2000);
        $this->actingAs($this->userOf(Clubhouse::factory()->create()), 'api');

        $this->graphQL(self::MUTATION, ['teamRoundId' => $this->teamRound->id])
            ->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    private const MUTATION = /** @lang GraphQL */ '
        mutation ($teamRoundId: ID!, $scenarioId: ID) {
          validateTeamRoundCrossSquads(teamRoundId: $teamRoundId, scenarioId: $scenarioId) {
            refId
            belowPlayer { refId category balance }
          }
        }
    ';

    private function validate(): array
    {
        return $this->graphQL(self::MUTATION, ['teamRoundId' => $this->teamRound->id])
            ->assertGraphQLErrorFree()
            ->json('data.validateTeamRoundCrossSquads');
    }

    /**
     * A Squad with one player in 1. HS and the player's stored points from the given Ranking List.
     */
    private function squad(int $order, ?string $version, string $refId, string $name, string $pointsVersion, int $points): Squad
    {
        $squad = Squad::query()->create([
            'team_round_id' => $this->teamRound->id,
            'playerLimit' => 10,
            'order' => $order,
            'name' => "Hold {$order}",
            'version' => $version,
        ]);
        $player = $squad->categories()->create(['category' => 'HS', 'name' => '1. HS'])
            ->players()->create(['name' => $name, 'gender' => 'M', 'member_ref_id' => $refId]);
        $player->points()->create([
            'category' => 'HS',
            'points' => $points,
            'position' => 1,
            'vintage' => 'SEN',
            'version' => $pointsVersion,
        ]);

        return $squad;
    }

    private function rankingPoints(string $refId, string $version, int $points): void
    {
        Point::query()->create([
            'member_id' => Member::query()->where('refId', $refId)->value('id'),
            'category' => 'HS',
            'points' => $points,
            'position' => 1,
            'vintage' => 'SEN',
            'version' => $version,
        ]);
    }

    private function member(string $refId, string $name): void
    {
        Member::query()->create([
            'refId' => $refId,
            'name' => $name,
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
    }

    private function userOf(Clubhouse $clubhouse): User
    {
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);

        return $user;
    }
}
