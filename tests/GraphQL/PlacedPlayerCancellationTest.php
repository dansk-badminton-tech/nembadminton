<?php

namespace Tests\GraphQL;

use App\Models\Cancellation;
use App\Models\CancellationCollector;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\TeamRound;
use App\Models\User;
use FlyCompany\TeamFight\GraphQL\Queries\PlacedPlayerCancellationResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PlacedPlayerCancellationTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    private Clubhouse $clubhouse;
    private User $user;
    private TeamRound $teamRound;
    private SquadCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clubhouse = Clubhouse::factory()->create();
        $this->user = User::factory()->create(['clubhouse_id' => $this->clubhouse->id]);
        $this->actingAs($this->user);

        $this->teamRound = TeamRound::factory()->create([
            'clubhouse_id' => $this->clubhouse->id,
            'user_id' => $this->user->id,
            'game_date' => '2026-10-12',
        ]);
        $squad = Squad::query()->create([
            'team_round_id' => $this->teamRound->id,
            'playerLimit' => 10,
            'order' => 1,
            'name' => '1. Hold',
        ]);
        $this->category = $squad->categories()->create(['name' => '1. HS', 'category' => 'HS']);

        PlacedPlayerCancellationResolver::clearCache();
    }

    /**
     * @test
     */
    public function it_marks_a_placed_player_with_an_afbudslink_cancellation_on_the_game_date(): void
    {
        $collector = $this->createCollector($this->clubhouse);
        $this->createLinkCancellation($this->placePlayer('ref-link'), $collector, ['2026-10-05', '2026-10-12']);

        $this->assertSame(['permanent' => false, 'viaCancellationLink' => true], $this->cancellationOf('ref-link'));
    }

    /**
     * @test
     */
    public function it_marks_a_placed_player_with_a_manual_cancellation_for_this_team_round(): void
    {
        $member = $this->placePlayer('ref-man');
        Cancellation::query()->create(['refId' => $member->refId, 'team_round_id' => $this->teamRound->id])
            ->dates()->create(['date' => '2026-10-12']);

        $this->assertSame(['permanent' => false, 'viaCancellationLink' => false], $this->cancellationOf('ref-man'));
    }

    /**
     * @test
     */
    public function it_marks_a_placed_player_with_a_permanent_cancellation(): void
    {
        $this->placePlayer('ref-perm', playable: false);

        $this->assertSame(['permanent' => true, 'viaCancellationLink' => false], $this->cancellationOf('ref-perm'));
    }

    /**
     * @test
     */
    public function it_ignores_cancellations_that_do_not_apply_to_this_team_round(): void
    {
        $this->placePlayer('ref-free');

        $collector = $this->createCollector($this->clubhouse);
        $this->createLinkCancellation($this->placePlayer('ref-date'), $collector, ['2026-10-13']);

        $otherClubhouseCollector = $this->createCollector(Clubhouse::factory()->create());
        $this->createLinkCancellation($this->placePlayer('ref-club'), $otherClubhouseCollector, ['2026-10-12']);

        $otherTeamRound = TeamRound::factory()->create([
            'clubhouse_id' => $this->clubhouse->id,
            'user_id' => $this->user->id,
            'game_date' => '2026-10-12',
        ]);
        $member = $this->placePlayer('ref-round');
        Cancellation::query()->create(['refId' => $member->refId, 'team_round_id' => $otherTeamRound->id])
            ->dates()->create(['date' => '2026-10-12']);

        foreach (['ref-free', 'ref-date', 'ref-club', 'ref-round'] as $refId) {
            $this->assertNull($this->cancellationOf($refId), $refId);
        }
    }

    private function placePlayer(string $refId, bool $playable = true): Member
    {
        $member = Member::query()->create([
            'name' => "Spiller $refId",
            'refId' => $refId,
            'gender' => 'M',
            'playable' => $playable,
        ]);
        $this->category->players()->create([
            'member_ref_id' => $member->refId,
            'name' => $member->name,
            'gender' => 'MEN',
        ]);

        return $member;
    }

    private function createCollector(Clubhouse $clubhouse): CancellationCollector
    {
        return CancellationCollector::query()->create([
            'email' => 'afbud@example.com',
            'user_id' => $this->user->id,
            'clubhouse_id' => $clubhouse->id,
        ]);
    }

    private function createLinkCancellation(Member $member, CancellationCollector $collector, array $dates): void
    {
        $cancellation = new Cancellation(['refId' => $member->refId, 'email' => 'spiller@example.com']);
        $cancellation->cancellationCollector()->associate($collector);
        $cancellation->save();

        foreach ($dates as $date) {
            $cancellation->dates()->create(['date' => $date]);
        }
    }

    private function cancellationOf(string $refId): ?array
    {
        $response = $this->graphQL(/** @lang GraphQL */ '
            query($id: ID!) {
                teamRound(id: $id) {
                    squads {
                        categories {
                            players {
                                refId
                                cancellation {
                                    permanent
                                    viaCancellationLink
                                }
                            }
                        }
                    }
                }
            }
        ', ['id' => $this->teamRound->id]);

        if (isset($response->json()['errors'])) {
            $this->fail(json_encode($response->json()['errors']));
        }

        $player = collect($response->json('data.teamRound.squads.0.categories.0.players'))->firstWhere('refId', $refId);
        $this->assertNotNull($player, "Player $refId is not placed");

        return $player['cancellation'];
    }
}
