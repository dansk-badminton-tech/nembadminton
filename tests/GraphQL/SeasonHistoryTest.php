<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Season;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeasonHistoryTest extends TestCase
{
    use MakesGraphQLRequests;
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
        setPermissionsTeamId($this->clubhouse->id);
        $this->user->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($this->user, 'api');
        $this->member('0101011111', 'Anders');
    }

    #[Test]
    public function it_returns_the_earlier_rounds_oldest_first_and_the_viewed_round_last(): void
    {
        $team = Team::factory()->withCustomTier('2. division')->create(['name' => 'Højbjerg 1', 'clubhouse_id' => $this->clubhouse->id]);
        foreach ([4 => '2026-11-01', 2 => '2026-10-04', 1 => '2026-09-20', 3 => '2026-10-18'] as $round => $date) {
            $earlier = $this->teamRound(round: $round, attributes: ['game_date' => $date]);
            $this->category($this->squad($earlier, 'x', order: 1, team: $team), 'HS', '1. HS', ['0101011111']);
        }
        $viewed = $this->teamRound(round: 5, attributes: ['name' => 'Femte runde', 'game_date' => '2026-11-15']);
        $this->category($this->squad($viewed, 'x', order: 1, team: $team), 'HS', '1. HS', ['0101011111']);

        $rounds = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory');

        $this->assertSame(
            [
                ['Runde 1', '2026-09-20', 1, 'GRUNDSPIL', false],
                ['Runde 2', '2026-10-04', 2, 'GRUNDSPIL', false],
                ['Runde 3', '2026-10-18', 3, 'GRUNDSPIL', false],
                ['Runde 4', '2026-11-01', 4, 'GRUNDSPIL', false],
                ['Femte runde', '2026-11-15', 5, 'GRUNDSPIL', true],
            ],
            array_map(fn (array $round) => [$round['name'], $round['gameDate'], $round['round'], $round['phase'], $round['isCurrent']], $rounds),
        );
        $this->assertSame($viewed->id, $rounds[4]['id']);
        $this->assertSame(
            [
                'category' => 'HS',
                'places' => [[
                    'place' => 1,
                    'squadPosition' => 1,
                    'teamName' => 'Højbjerg 1',
                    'tier' => '2. division',
                    'isPaperSquad' => false,
                    'label' => '1. HS',
                    'players' => [['refId' => '0101011111', 'name' => 'Anders', 'gender' => 'MEN', 'vintage' => 'SEN']],
                ]],
            ],
            $rounds[0]['categories'][0],
        );
    }

    #[Test]
    public function a_place_on_a_paper_squad_is_marked(): void
    {
        $team = Team::factory()->create(['name' => 'Højbjerg 2', 'clubhouse_id' => $this->clubhouse->id]);
        $viewed = $this->teamRound(round: 1);
        $squad = $this->squad($viewed, 'x', order: 1, team: $team);
        $squad->update(['is_paper_squad' => true]);
        $this->category($squad, 'HS', '1. HS', ['0101011111']);

        $places = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory.0.categories.0.places');

        $this->assertSame([['Højbjerg 2', true]], array_map(fn (array $place) => [$place['teamName'], $place['isPaperSquad']], $places));
    }

    #[Test]
    public function places_follow_team_order_and_partners_share_a_place(): void
    {
        $this->member('0101012222', 'Bo');
        $this->member('0101013333', 'Carl');
        $this->member('0202021111', 'Eva', 'K');
        $this->member('0202022222', 'Fie', 'K');
        $viewed = $this->teamRound(round: 1);
        // Created out of order to show the places follow Squad order and category number.
        $hold2 = $this->squad($viewed, 'Hold 2', order: 2, tier: 'Serie 1');
        $hold1 = $this->squad($viewed, 'Hold 1', order: 1, tier: 'Danmarksserien');
        $this->category($hold2, 'HS', '1. HS', ['0101013333']);
        $this->category($hold1, 'HS', '2. HS', ['0101012222']);
        $this->category($hold1, 'HS', '1. HS', ['0101011111']);
        $this->category($hold1, 'HD', '1. HD', ['0101011111', '0101012222']);
        $this->category($hold1, 'DD', '1. DD', ['0202021111', '0202022222']);
        $this->category($hold1, 'MD', '1. MD', ['0101013333', '0202021111']);

        $categories = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory.0.categories');

        $this->assertSame(['HS', 'DS', 'HD', 'DD', 'MD'], array_column($categories, 'category'));
        $this->assertSame(
            [
                [1, 'Hold 1', 'Danmarksserien', '1. HS', ['Anders']],
                [2, 'Hold 1', 'Danmarksserien', '2. HS', ['Bo']],
                [3, 'Hold 2', 'Serie 1', '1. HS', ['Carl']],
            ],
            $this->places($categories[0]),
        );
        $this->assertSame([1, 1, 2], array_column($categories[0]['places'], 'squadPosition'));
        $this->assertSame([], $categories[1]['places']);
        $this->assertSame([[1, 'Hold 1', 'Danmarksserien', '1. HD', ['Anders', 'Bo']]], $this->places($categories[2]));
        $this->assertSame([[1, 'Hold 1', 'Danmarksserien', '1. DD', ['Eva', 'Fie']]], $this->places($categories[3]));
        $this->assertSame([[1, 'Hold 1', 'Danmarksserien', '1. MD', ['Carl', 'Eva']]], $this->places($categories[4]));
    }

    #[Test]
    public function a_player_only_in_a_draft_scenario_does_not_appear(): void
    {
        $this->member('0101012222', 'Bo');
        $earlier = $this->teamRound(round: 1, attributes: ['game_date' => '2026-09-20']);
        $official = $earlier->scenarios()->create(['name' => 'Plan A', 'is_official' => true]);
        $draft = $earlier->scenarios()->create(['name' => 'Plan B', 'is_official' => false]);
        $squad = $this->squad($earlier, 'Hold 1', order: 1);
        $this->category($squad, 'HS', '1. HS', ['0101011111'], $official->id);
        $this->category($squad, 'HS', '1. HS', ['0101012222'], $draft->id);
        $viewed = $this->teamRound(round: 2, attributes: ['game_date' => '2026-10-04']);

        $hs = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory.0.categories.0');

        $this->assertSame([[1, 'Hold 1', null, '1. HS', ['Anders']]], $this->places($hs));
    }

    #[Test]
    public function only_earlier_rounds_of_the_same_season_count(): void
    {
        Season::query()->firstOrCreate(['id' => 2025], ['season_name' => '2025/2026']);
        $this->teamRound(round: 1, attributes: ['name' => 'earlier', 'game_date' => '2026-09-20']);
        $this->teamRound(round: 2, attributes: ['name' => 'same date', 'game_date' => '2026-10-04']);
        $this->teamRound(round: 3, attributes: ['name' => 'later', 'game_date' => '2026-10-18']);
        $this->teamRound(round: 1, attributes: ['name' => 'other season', 'season_id' => 2025, 'game_date' => '2026-03-01']);
        // Its game date falls in the viewed Season, but a TeamRound without a Season never counts.
        $this->teamRound(round: 1, attributes: ['name' => 'no season', 'season_id' => null, 'game_date' => '2026-09-27']);
        $viewed = $this->teamRound(round: 2, attributes: ['name' => 'viewed', 'game_date' => '2026-10-04']);

        $rounds = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory');

        $this->assertSame([['earlier', false], ['viewed', true]], array_map(fn (array $round) => [$round['name'], $round['isCurrent']], $rounds));
    }

    #[Test]
    public function a_viewed_round_without_a_season_or_game_date_returns_only_itself(): void
    {
        $this->teamRound(round: 1, attributes: ['name' => 'earlier', 'game_date' => '2026-09-20']);
        $withoutSeason = $this->teamRound(round: 2, attributes: ['name' => 'no season', 'season_id' => null, 'game_date' => '2026-10-04']);
        $withoutDate = $this->teamRound(round: 2, attributes: ['name' => 'no date', 'game_date' => null]);

        foreach ([$withoutSeason, $withoutDate] as $viewed) {
            $rounds = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory');

            $this->assertSame([[$viewed->name, true]], array_map(fn (array $round) => [$round['name'], $round['isCurrent']], $rounds));
        }
    }

    #[Test]
    public function a_round_without_a_round_number_has_no_round_or_phase(): void
    {
        $this->teamRound(round: null, attributes: ['name' => 'Ekstra runde', 'game_date' => '2026-09-20']);
        $viewed = $this->teamRound(round: 2, attributes: ['game_date' => '2026-10-04']);

        $rounds = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory');

        $this->assertSame(['Ekstra runde', null, null], [$rounds[0]['name'], $rounds[0]['round'], $rounds[0]['phase']]);
    }

    #[Test]
    public function team_rounds_of_another_clubhouse_are_never_included(): void
    {
        $otherClubhouse = Clubhouse::factory()->create();
        $otherUser = User::factory()->create(['clubhouse_id' => $otherClubhouse->id]);
        $foreign = $this->teamRound(round: 1, attributes: [
            'name' => 'foreign',
            'game_date' => '2026-09-20',
            'clubhouse_id' => $otherClubhouse->id,
            'user_id' => $otherUser->id,
        ]);
        // The same Member stands in both Clubhouses' lineups.
        $this->category($this->squad($foreign, 'Fremmed hold', order: 1), 'HS', '1. HS', ['0101011111']);
        $viewed = $this->teamRound(round: 2, attributes: ['name' => 'viewed', 'game_date' => '2026-10-04']);
        $this->category($this->squad($viewed, 'Hold 1', order: 1), 'HS', '1. HS', ['0101011111']);

        $rounds = $this->seasonHistory($viewed)->json('data.teamRound.seasonHistory');

        $this->assertSame(['viewed'], array_column($rounds, 'name'));
    }

    #[Test]
    public function a_user_of_another_clubhouse_cannot_read_the_season_history(): void
    {
        $otherClubhouse = Clubhouse::factory()->create();
        $otherUser = User::factory()->create(['clubhouse_id' => $otherClubhouse->id]);
        $this->teamRound(round: 1, attributes: ['game_date' => '2026-09-20']);
        $viewed = $this->teamRound(round: 2, attributes: ['game_date' => '2026-10-04']);
        setPermissionsTeamId($otherClubhouse->id);
        $otherUser->givePermissionTo(Permission::VIEW_TEAMROUNDS->value);
        $this->actingAs($otherUser, 'api');

        $response = $this->seasonHistory($viewed);

        $response->assertGraphQLErrorMessage('This action is unauthorized.');
        $this->assertNull($response->json('data.teamRound.seasonHistory'));
    }

    #[Test]
    public function a_guest_with_the_public_link_cannot_read_the_season_history(): void
    {
        $viewed = $this->teamRound(round: 1);
        $this->app['auth']->forgetGuards();

        $response = $this->seasonHistory($viewed);

        $response->assertGraphQLErrorMessage('Unauthenticated.');
        $this->assertNull($response->json('data.teamRound.seasonHistory'));
    }

    #[Test]
    public function a_player_sees_it_only_for_rounds_they_stand_in(): void
    {
        $player = User::factory()->create(['clubhouse_id' => $this->clubhouse->id, 'player_id' => '0101011111']);
        $player->assignRole(Role::PLAYER->value);
        $player->update(['primary_role_id' => \Spatie\Permission\Models\Role::query()->where('name', Role::PLAYER->value)->firstOrFail()->id]);
        $this->teamRound(round: 1, attributes: ['name' => 'earlier', 'game_date' => '2026-09-20']);
        $playing = $this->teamRound(round: 2, attributes: ['name' => 'playing', 'game_date' => '2026-10-04']);
        $this->category($this->squad($playing, 'Hold 1', order: 1), 'HS', '1. HS', ['0101011111']);
        $notPlaying = $this->teamRound(round: 2, attributes: ['name' => 'not playing', 'game_date' => '2026-10-04']);
        $this->actingAs($player, 'api');

        $this->assertSame(['earlier', 'playing'], array_column($this->seasonHistory($playing)->json('data.teamRound.seasonHistory'), 'name'));
        $this->seasonHistory($notPlaying)->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    #[Test]
    public function a_bigger_season_loads_in_the_same_number_of_queries(): void
    {
        $this->member('0101012222', 'Bo');
        $viewed = $this->teamRound(round: 9, attributes: ['game_date' => '2027-03-01']);
        $this->seasonOfRounds(1);
        // Warm up, so the User's roles and permissions are cached before counting.
        $this->seasonHistory($viewed);
        $smallSeasonQueries = $this->countQueries(fn () => $this->seasonHistory($viewed)->assertJsonCount(2, 'data.teamRound.seasonHistory'));

        $this->seasonOfRounds(6);
        $bigSeasonQueries = $this->countQueries(fn () => $this->seasonHistory($viewed)->assertJsonCount(8, 'data.teamRound.seasonHistory'));

        $this->assertSame($smallSeasonQueries, $bigSeasonQueries);
    }

    private function seasonOfRounds(int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $round = $this->teamRound(round: 1, attributes: ['game_date' => '2026-09-20']);
            $official = $round->scenarios()->create(['name' => 'Plan A', 'is_official' => true]);
            $team = Team::factory()->withCustomTier('2. division')->create(['clubhouse_id' => $this->clubhouse->id]);
            foreach ([1, 2, 3] as $order) {
                $squad = $this->squad($round, "Hold $order", order: $order, team: $order === 1 ? $team : null);
                $this->category($squad, 'HD', '1. HD', ['0101011111', '0101012222'], $official->id);
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

    private function seasonHistory(TeamRound $teamRound): TestResponse
    {
        return $this->graphQL(/** @lang GraphQL */ '
            query ($id: ID!) {
                teamRound(id: $id) {
                    seasonHistory {
                        id
                        name
                        gameDate
                        round
                        phase
                        isCurrent
                        categories {
                            category
                            places {
                                place
                                squadPosition
                                teamName
                                tier
                                isPaperSquad
                                label
                                players { refId name gender vintage }
                            }
                        }
                    }
                }
            }
        ', ['id' => $teamRound->id]);
    }

    /**
     * A category's places as [place, team name, tier, label, player names].
     *
     * @param  array<string, mixed>  $category
     * @return list<array{int, string|null, string|null, string, list<string>}>
     */
    private function places(array $category): array
    {
        return array_map(fn (array $place) => [
            $place['place'],
            $place['teamName'],
            $place['tier'],
            $place['label'],
            array_column($place['players'], 'name'),
        ], $category['places']);
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
            'game_date' => '2026-09-20',
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
