<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Models\Clubhouse;
use App\Models\Season;
use App\Models\TeamRound;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * §31 stk. 1: A player must turn 15 no later than 31.12. in the calendar year the season starts.
 */
class TooYoungPlayersTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private const VALIDATE = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!, $seasonStartYear: Int!) {
          validateTooYoungPlayers(input: $input, seasonStartYear: $seasonStartYear) {
            id
            refId
            name
          }
        }
    ';

    private function player(string $id, string $refId, string $name): array
    {
        return [
            'id' => $id,
            'refId' => $refId,
            'name' => $name,
            'gender' => 'MEN',
            'points' => [],
        ];
    }

    private function lineup(): array
    {
        $young = $this->player('1', '120101-1111', 'Ung Spiller');
        $oldEnough = $this->player('2', '111231-2222', 'Gammel Nok');
        $adult = $this->player('3', '950101-3333', 'Voksen');

        return [
            [
                'name' => 'Hold 1',
                'squad' => [
                    'playerLimit' => 10,
                    'categories' => [
                        ['category' => 'HS', 'name' => '1. HS', 'players' => [$young]],
                        ['category' => 'HD', 'name' => '1. HD', 'players' => [$young, $oldEnough]],
                    ],
                ],
            ],
            [
                'name' => 'Hold 2',
                'squad' => [
                    'playerLimit' => 10,
                    'categories' => [
                        ['category' => 'HS', 'name' => '1. HS', 'players' => [$adult]],
                    ],
                ],
            ],
        ];
    }

    #[Test]
    public function itReportsEachTooYoungPlayerOnce(): void
    {
        $this->graphQL(self::VALIDATE, [
            'input' => $this->lineup(),
            'seasonStartYear' => 2026,
        ])->assertJsonPath('data.validateTooYoungPlayers', [
            ['id' => '1', 'refId' => '120101-1111', 'name' => 'Ung Spiller'],
        ]);
    }

    #[Test]
    public function itJudgesByTheGivenSeasonNotToday(): void
    {
        Carbon::withTestNow(Carbon::create(2026, 10, 1), function () {
            $this->graphQL(self::VALIDATE, [
                'input' => $this->lineup(),
                'seasonStartYear' => 2027,
            ])->assertJsonPath('data.validateTooYoungPlayers', []);
        });
    }

    #[Test]
    public function aTeamRoundExposesTheStartYearOfItsSeason(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create(['clubhouse_id' => $clubhouse->id]);
        setPermissionsTeamId($clubhouse->id);
        $this->actingAs($user, 'api');

        $season = Season::query()->firstOrCreate(['id' => 2027], ['season_name' => '2027/2028']);
        $withSeason = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => $season->id,
            'game_date' => '2027-09-12',
        ]);
        $springWithoutSeason = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => null,
            'game_date' => '2026-03-01',
        ]);
        $autumnWithoutSeason = TeamRound::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'user_id' => $user->id,
            'season_id' => null,
            'game_date' => '2026-08-01',
        ]);

        Carbon::withTestNow(Carbon::create(2026, 10, 1), function () use ($withSeason, $springWithoutSeason, $autumnWithoutSeason) {
            $query = /** @lang GraphQL */ 'query ($id: ID!) { teamRound(id: $id) { seasonStartYear } }';

            $this->graphQL($query, ['id' => $withSeason->id])
                ->assertJsonPath('data.teamRound.seasonStartYear', 2027);
            $this->graphQL($query, ['id' => $springWithoutSeason->id])
                ->assertJsonPath('data.teamRound.seasonStartYear', 2025);
            $this->graphQL($query, ['id' => $autumnWithoutSeason->id])
                ->assertJsonPath('data.teamRound.seasonStartYear', 2026);
        });
    }
}
