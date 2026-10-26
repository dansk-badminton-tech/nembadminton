<?php

namespace Tests\GraphQL;

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

class SquadMemberRankingPointsTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    #[Test]
    public function aPlacedPlayerHasTheMembersPointsOnTheGivenRanking(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $member = $this->member('020290-2222', 'Bo');
        // Only on the Squad's own March ranking.
        $newcomer = $this->member('030390-3333', 'Carl');
        foreach ([
            [$member, '2026-02-01', 'HS', 1940],
            [$member, '2026-02-01', null, 3000],
            [$member, '2026-03-01', 'HS', 2060],
            [$newcomer, '2026-03-01', 'HS', 1800],
        ] as [$owner, $version, $category, $points]) {
            Point::query()->create([
                'member_id' => $owner->id,
                'points' => $points,
                'position' => 1,
                'category' => $category,
                'vintage' => 'SEN',
                'version' => $version,
            ]);
        }

        $teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'version' => '2026-02-01',
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $teamRound->id,
            'playerLimit' => 10,
            'order' => 2,
            'name' => 'Hold 2',
            'version' => '2026-03-01',
        ]);
        $category = $squad->categories()->create(['category' => 'HS', 'name' => '1. HS']);
        $category->players()->create(['name' => 'Bo', 'gender' => 'M', 'member_ref_id' => $member->refId]);
        $category->players()->create(['name' => 'Carl', 'gender' => 'M', 'member_ref_id' => $newcomer->refId]);

        $this->graphQL(/** @lang GraphQL */ '
            query ($id: ID!, $version: Date!) {
              teamRound(id: $id) {
                squads {
                  categories {
                    players {
                      refId
                      rankingPoints(version: $version) { category points version }
                    }
                  }
                }
              }
            }
        ', ['id' => $teamRound->id, 'version' => '2026-02-01'])
            ->assertJsonPath('data.teamRound.squads.0.categories.0.players', [
                [
                    'refId' => '020290-2222',
                    'rankingPoints' => [
                        ['category' => 'HS', 'points' => 1940, 'version' => '2026-02-01'],
                        ['category' => null, 'points' => 3000, 'version' => '2026-02-01'],
                    ],
                ],
                ['refId' => '030390-3333', 'rankingPoints' => []],
            ]);
    }

    private function member(string $refId, string $name): Member
    {
        return Member::query()->create([
            'refId' => $refId,
            'name' => $name,
            'gender' => 'M',
            'birthday' => '1990-02-02',
            'playable' => true,
            'inactive' => false,
        ]);
    }
}
