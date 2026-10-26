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

/**
 * The paper-team rule (§38 stk. 7): a player on a Paper Squad stands only in categories they have played this season,
 * and has stood on the same or a lower Team in earlier TeamRounds.
 */
class ValidatePaperSquadsTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    private const SEASON = 2026;

    private const ANDERS = '0101011111';

    private const BO = '0101012222';

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Clubhouse $clubhouse;

    private User $user;

    private Team $team1;

    private Team $team2;

    protected function setUp(): void
    {
        parent::setUp();
        Season::query()->firstOrCreate(['id' => self::SEASON], ['season_name' => '2026/2027']);
        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = $this->userOf($this->clubhouse);
        $this->actingAs($this->user, 'api');
        $this->team1 = Team::factory()->withCustomTier('2. division')->create(['clubhouse_id' => $this->clubhouse->id, 'name' => 'Højbjerg 1']);
        $this->team2 = Team::factory()->withCustomTier('3. division')->create(['clubhouse_id' => $this->clubhouse->id, 'name' => 'Højbjerg 2']);
        $this->member(self::ANDERS, 'Anders');
        $this->member(self::BO, 'Bo');
    }

    #[Test]
    public function a_player_who_played_the_category_on_the_same_team_meets_the_rule(): void
    {
        $this->playedEarlier('2026-09-27', round: 1, onTeam: 1, category: 'HS', refIds: [self::ANDERS]);
        [$round, $paper] = $this->paperRound(round: 2);

        $this->validate($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)])
            ->assertJsonPath('data.validatePaperSquads', ['missingRound' => false, 'players' => [], 'withoutRound' => []]);
    }

    #[Test]
    public function a_category_the_player_has_not_played_this_season_is_named(): void
    {
        $this->playedEarlier('2026-09-27', round: 1, onTeam: 1, category: 'HS', refIds: [self::ANDERS]);
        [$round, $paper] = $this->paperRound(round: 2);

        $players = $this->players($round, [$this->lineup($paper, [
            ['HS', '1. HS', [self::ANDERS]],
            ['HD', '1. HD', [self::ANDERS, self::BO]],
        ], isPaperSquad: true)]);

        $this->assertSame([
            ['Anders', ['HD'], 1, 1],
            ['Bo', ['HD'], 0, 1],
        ], $players);
    }

    #[Test]
    public function round_1_is_exempt(): void
    {
        [$round, $paper] = $this->paperRound(round: 1);

        $this->assertSame([], $this->players($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)]));
    }

    #[Test]
    public function from_round_2_the_group_stage_needs_one_earlier_match_on_the_same_or_a_lower_team(): void
    {
        $this->playedEarlier('2026-09-20', round: 1, onTeam: 2, category: 'HS', refIds: [self::ANDERS]);
        $this->playedEarlier('2026-09-21', round: 1, onTeam: 1, category: 'HS', refIds: [self::BO], name: 'Bo spiller kun i en anden klubs runde', clubhouse: Clubhouse::factory()->create());
        [$round, $paper] = $this->paperRound(round: 2);

        $players = $this->players($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]], ['HS', '2. HS', [self::BO]]], isPaperSquad: true)]);

        // Anders stood on the lower Team; Bo's only match was in another Clubhouse, which doesn't count.
        $this->assertSame([['Bo', ['HS'], 0, 1]], $players);
    }

    #[Test]
    public function a_placement_on_a_higher_team_does_not_count(): void
    {
        $this->playedEarlier('2026-09-27', round: 1, onTeam: 1, category: 'HS', refIds: [self::ANDERS]);
        [$round, $paper] = $this->paperRound(round: 2, paperTeam: 2);

        $response = $this->validate($round, [
            $this->lineup($round->squads()->where('team_id', $this->team1->id)->sole(), []),
            $this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true),
        ]);

        $response->assertJsonPath('data.validatePaperSquads.players', [[
            'squadIndex' => 1,
            'refId' => self::ANDERS,
            'name' => 'Anders',
            'unplayedCategories' => [],
            'matchCount' => 0,
            'requiredMatchCount' => 1,
            'teamName' => 'Højbjerg 2',
            'uncountedRounds' => [],
        ]]);
    }

    #[Test]
    public function earlier_rounds_without_a_squad_for_the_paper_squads_team_are_named_so_they_can_be_linked(): void
    {
        $this->playedEarlier('2026-09-20', round: 1, onTeam: 1, category: 'HS', refIds: [self::ANDERS], withTeams: false);
        $this->playedEarlier('2026-09-27', round: null, onTeam: 1, category: 'HS', refIds: [self::ANDERS], name: 'Ekstra runde', withTeams: false);
        $this->playedEarlier('2026-10-04', round: 2, onTeam: 1, category: 'HS', refIds: [self::BO]);
        [$round, $paper] = $this->paperRound(round: 3);

        $response = $this->validate($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]], ['HS', '2. HS', [self::BO]]], isPaperSquad: true)]);

        $players = $response->json('data.validatePaperSquads.players');
        $this->assertSame(['Anders'], array_column($players, 'name'));
        $this->assertSame(0, $players[0]['matchCount']);
        $this->assertSame('Højbjerg 1', $players[0]['teamName']);
        $this->assertSame([[1, 'Runde 1']], array_map(
            fn (array $uncounted) => [$uncounted['round'], $uncounted['name']],
            $players[0]['uncountedRounds'],
        ));
    }

    #[Test]
    public function earlier_rounds_without_a_round_number_are_left_out_and_named(): void
    {
        $this->playedEarlier('2026-09-27', round: null, onTeam: 1, category: 'HS', refIds: [self::ANDERS], name: 'Ekstra runde');
        [$round, $paper] = $this->paperRound(round: 2);

        $validation = $this->validate($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)])
            ->json('data.validatePaperSquads');

        $this->assertSame([['Anders', ['HS'], 0, 1]], array_map(
            fn (array $player) => [$player['name'], $player['unplayedCategories'], $player['matchCount'], $player['requiredMatchCount']],
            $validation['players'],
        ));
        $this->assertSame(['Ekstra runde'], array_column($validation['withoutRound'], 'name'));
    }

    #[Test]
    public function a_round_of_another_clubhouse_is_not_named(): void
    {
        $this->playedEarlier('2026-09-21', round: 1, onTeam: 1, category: 'HS', refIds: [self::ANDERS], name: 'En anden klubs runde', clubhouse: Clubhouse::factory()->create(), withTeams: false);
        [$round, $paper] = $this->paperRound(round: 2);

        $players = $this->validate($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)])
            ->json('data.validatePaperSquads.players');

        $this->assertSame(0, $players[0]['matchCount']);
        $this->assertSame([], $players[0]['uncountedRounds']);
    }

    #[Test]
    public function rounds_without_the_team_are_not_named_when_the_player_has_enough_matches(): void
    {
        $this->playedEarlier('2026-09-20', round: 1, onTeam: 1, category: 'HS', refIds: [self::ANDERS], withTeams: false);
        $this->playedEarlier('2026-09-27', round: 2, onTeam: 1, category: 'HS', refIds: [self::ANDERS]);
        [$round, $paper] = $this->paperRound(round: 3);

        $players = $this->validate($round, [$this->lineup($paper, [['HD', '1. HD', [self::ANDERS]]], isPaperSquad: true)])
            ->json('data.validatePaperSquads.players');

        $this->assertSame(['HD'], $players[0]['unplayedCategories']);
        $this->assertSame([], $players[0]['uncountedRounds']);
    }

    #[Test]
    public function the_playoffs_need_two_earlier_matches_on_the_same_or_a_lower_team(): void
    {
        $this->playedEarlier('2026-09-27', round: 6, onTeam: 1, category: 'HS', refIds: [self::ANDERS, self::BO]);
        $this->playedEarlier('2026-10-04', round: 7, onTeam: 2, category: 'HS', refIds: [self::ANDERS]);
        [$round, $paper] = $this->paperRound(round: 8);

        $players = $this->players($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]], ['HS', '2. HS', [self::BO]]], isPaperSquad: true)]);

        $this->assertSame([['Bo', [], 1, 2]], $players);
    }

    #[Test]
    public function a_team_round_without_a_round_number_asks_for_it_instead_of_checking(): void
    {
        [$round, $paper] = $this->paperRound(round: null);

        $this->validate($round, [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)])
            ->assertJsonPath('data.validatePaperSquads', ['missingRound' => true, 'players' => [], 'withoutRound' => []]);
    }

    #[Test]
    public function a_regular_squad_is_not_checked(): void
    {
        $round = $this->teamRound(round: 2, gameDate: '2026-10-11');
        $squad = $this->squad($round, $this->team1, order: 1);

        $this->validate($round, [$this->lineup($squad, [['HS', '1. HS', [self::ANDERS]]])])
            ->assertJsonPath('data.validatePaperSquads', ['missingRound' => false, 'players' => [], 'withoutRound' => []]);
    }

    #[Test]
    public function the_paper_squad_flag_is_read_from_the_squad_not_the_input(): void
    {
        $round = $this->teamRound(round: 2, gameDate: '2026-10-11');
        $squad = $this->squad($round, $this->team1, order: 1);

        $this->validate($round, [$this->lineup($squad, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)])
            ->assertJsonPath('data.validatePaperSquads.players', []);
    }

    #[Test]
    public function it_denies_validating_a_team_round_in_another_clubhouse(): void
    {
        [$round, $paper] = $this->paperRound(round: 2);
        $this->actingAs($this->userOf(Clubhouse::factory()->create()), 'api');

        $this->graphQL($this->mutation(), [
            'teamRoundId' => $round->id,
            'input' => [$this->lineup($paper, [['HS', '1. HS', [self::ANDERS]]], isPaperSquad: true)],
        ])->assertGraphQLErrorMessage('This action is unauthorized.');
    }

    #[Test]
    public function the_rank_order_on_the_team_is_not_checked_for_a_paper_squad(): void
    {
        // Bo has far more HS points than Anders, but stands below him.
        $lineup = $this->lineup(null, [['HS', '1. HS', [self::ANDERS]], ['HS', '2. HS', [self::BO]]], points: [self::ANDERS => 100, self::BO => 3000]);

        $query = 'mutation($input: [ValidateTeam!]!) { validateSquads(input: $input) { refId } }';
        $this->assertNotEmpty($this->graphQL($query, ['input' => [$lineup]])->json('data.validateSquads'));

        $lineup['squad']['isPaperSquad'] = true;
        $this->graphQL($query, ['input' => [$lineup]])->assertJsonPath('data.validateSquads', []);
    }

    #[Test]
    public function a_paper_squad_is_not_checked_against_higher_teams_but_lower_teams_are_checked_against_it(): void
    {
        $query = 'mutation($input: [ValidateTeam!]!) { validateCrossSquads(input: $input) { refId } }';
        $strong = '0101013333';
        $points = [self::ANDERS => 100, self::BO => 100, $strong => 3000];
        $higher = $this->lineup(null, [['HS', '1. HS', [self::ANDERS]]], points: $points);
        $paper = $this->lineup(null, [['HS', '1. HS', [$strong]]], isPaperSquad: true, points: $points);
        $paperAboveLower = $this->lineup(null, [['HS', '1. HS', [self::BO]]], isPaperSquad: true, points: $points);
        $lower = $this->lineup(null, [['HS', '1. HS', [$strong]]], points: $points);

        $this->graphQL($query, ['input' => [$higher, $paper]])->assertJsonPath('data.validateCrossSquads', []);
        $paper['squad']['isPaperSquad'] = false;
        $this->assertSame([$strong], array_column($this->graphQL($query, ['input' => [$higher, $paper]])->json('data.validateCrossSquads'), 'refId'));
        $this->assertSame([$strong], array_column($this->graphQL($query, ['input' => [$paperAboveLower, $lower]])->json('data.validateCrossSquads'), 'refId'));
    }

    /**
     * The players flagged, as [name, unplayed categories, match count, required match count].
     *
     * @param  list<array<string, mixed>>  $input
     * @return list<array{string, list<string>, int, int}>
     */
    private function players(TeamRound $round, array $input): array
    {
        $response = $this->validate($round, $input);
        $this->assertFalse($response->json('data.validatePaperSquads.missingRound'));

        return array_map(fn (array $player) => [
            $player['name'],
            $player['unplayedCategories'],
            $player['matchCount'],
            $player['requiredMatchCount'],
        ], $response->json('data.validatePaperSquads.players'));
    }

    /**
     * @param  list<array<string, mixed>>  $input
     */
    private function validate(TeamRound $round, array $input): TestResponse
    {
        return $this->graphQL($this->mutation(), ['teamRoundId' => $round->id, 'input' => $input])->assertGraphQLErrorFree();
    }

    private function mutation(): string
    {
        return /** @lang GraphQL */ '
            mutation($teamRoundId: ID!, $input: [ValidateTeam!]!) {
                validatePaperSquads(teamRoundId: $teamRoundId, input: $input) {
                    missingRound
                    players { squadIndex refId name unplayedCategories matchCount requiredMatchCount teamName uncountedRounds { teamRoundId name round } }
                    withoutRound { id name gameDate }
                }
            }
        ';
    }

    /**
     * A TeamRound on 2026-10-11 with Team 1 then Team 2, one of them a Paper Squad.
     *
     * @return array{TeamRound, Squad}
     */
    private function paperRound(?int $round, int $paperTeam = 1): array
    {
        $teamRound = $this->teamRound($round, '2026-10-11');
        $squad1 = $this->squad($teamRound, $this->team1, order: 1, isPaperSquad: $paperTeam === 1);
        $squad2 = $this->squad($teamRound, $this->team2, order: 2, isPaperSquad: $paperTeam === 2);

        return [$teamRound, $paperTeam === 1 ? $squad1 : $squad2];
    }

    /**
     * An earlier TeamRound with Team 1 then Team 2, the players standing in one category on one of them.
     * Without teams, its two Squads have no Team linked.
     *
     * @param  list<string>  $refIds
     */
    private function playedEarlier(string $gameDate, ?int $round, int $onTeam, string $category, array $refIds, ?string $name = null, ?Clubhouse $clubhouse = null, bool $withTeams = true): void
    {
        $teamRound = $this->teamRound($round, $gameDate, $name, $clubhouse);
        $squads = [
            1 => $this->squad($teamRound, $withTeams ? $this->team1 : null, order: 1),
            2 => $this->squad($teamRound, $withTeams ? $this->team2 : null, order: 2),
        ];
        foreach ($refIds as $index => $refId) {
            $squadCategory = $squads[$onTeam]->categories()->create(['category' => $category, 'name' => ($index + 1).'. '.$category]);
            $member = Member::query()->where('refId', $refId)->sole();
            $squadCategory->players()->create(['name' => $member->name, 'gender' => $member->gender, 'member_ref_id' => $refId]);
        }
    }

    private function teamRound(?int $round, string $gameDate, ?string $name = null, ?Clubhouse $clubhouse = null): TeamRound
    {
        return TeamRound::factory()->create([
            'clubhouse_id' => ($clubhouse ?? $this->clubhouse)->id,
            'user_id' => $this->user->id,
            'season_id' => self::SEASON,
            'round' => $round,
            'game_date' => $gameDate,
            'name' => $name,
        ]);
    }

    private function squad(TeamRound $round, ?Team $team, int $order, bool $isPaperSquad = false): Squad
    {
        $squad = Squad::query()->create([
            'team_round_id' => $round->id,
            'playerLimit' => 10,
            'team_id' => $team?->id,
            'is_paper_squad' => $isPaperSquad,
        ]);
        // Sortable assigns the next order on create, so set the wanted order afterwards.
        $squad->update(['order' => $order]);

        return $squad;
    }

    /**
     * A ValidateTeam input, as the lineup editor sends it.
     *
     * @param  list<array{string, string, list<string>}>  $categories  [category, name, refIds]
     * @param  array<string, int>  $points  HS points per refId
     * @return array<string, mixed>
     */
    private function lineup(?Squad $squad, array $categories, bool $isPaperSquad = false, array $points = []): array
    {
        return [
            'name' => 'Team X',
            'squad' => [
                'id' => $squad === null ? null : (string) $squad->id,
                'playerLimit' => 10,
                'isPaperSquad' => $isPaperSquad,
                'categories' => array_map(fn (array $category) => [
                    'category' => $category[0],
                    'name' => $category[1],
                    'players' => array_map(fn (string $refId) => $this->player($refId, $points[$refId] ?? 0), $category[2]),
                ], $categories),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function player(string $refId, int $hsPoints): array
    {
        return [
            'refId' => $refId,
            'name' => Member::query()->where('refId', $refId)->value('name') ?? $refId,
            'gender' => 'MEN',
            'points' => $hsPoints === 0 ? [] : [['category' => 'HS', 'points' => $hsPoints, 'position' => 1, 'vintage' => 'SEN']],
        ];
    }

    private function member(string $refId, string $name): void
    {
        Member::query()->create([
            'refId' => $refId,
            'name' => $name,
            'gender' => 'M',
            'birthday' => '2001-01-01',
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
