<?php

namespace Tests\GraphQL;

use App\Enums\TournamentPhaseType;
use App\Models\Season;
use App\Models\TournamentGroup;
use App\Models\TournamentTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class TournamentGroupsTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    /**
     * @test
     */
    public function it_can_query_tournament_groups_for_a_specific_season(): void
    {
        $season2025 = Season::query()->create([
            'id' => 2025,
            'season_name' => '2025/2026',
        ]);
        $season2026 = Season::query()->create([
            'id' => 2026,
            'season_name' => '2026/2027',
        ]);

        $liga = TournamentTier::query()->create([
            'tier_name' => 'Badmintonligaen',
            'rank_level' => 1,
        ]);

        TournamentGroup::query()->create([
            'season_id' => $season2025->id,
            'tier_id' => $liga->id,
            'group_name' => 'Pulje 1',
            'phase_type' => TournamentPhaseType::REGULAR_SEASON->value,
        ]);
        TournamentGroup::query()->create([
            'season_id' => $season2025->id,
            'tier_id' => null,
            'group_name' => 'Oversidder-runder/papirhold',
            'phase_type' => TournamentPhaseType::BYES_PAPER_TEAM->value,
        ]);
        TournamentGroup::query()->create([
            'season_id' => $season2026->id,
            'tier_id' => $liga->id,
            'group_name' => 'Kvartfinaler',
            'phase_type' => TournamentPhaseType::PLAYOFF->value,
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($seasonId: Int!) {
                tournamentGroups(seasonId: $seasonId) {
                    seasonId
                    groupName
                    phaseType
                    tournamentTier {
                        tierName
                        rankLevel
                    }
                }
            }
        ', [
            'seasonId' => 2025,
        ]);

        $response->assertJsonMissingPath('errors');
        $response->assertJsonCount(2, 'data.tournamentGroups');
        $response->assertJsonFragment([
            'seasonId' => 2025,
            'groupName' => 'Pulje 1',
            'phaseType' => TournamentPhaseType::REGULAR_SEASON->value,
            'tierName' => 'Badmintonligaen',
            'rankLevel' => 1,
        ]);
        $response->assertJsonFragment([
            'seasonId' => 2025,
            'groupName' => 'Oversidder-runder/papirhold',
            'phaseType' => TournamentPhaseType::BYES_PAPER_TEAM->value,
        ]);
    }

    /**
     * @test
     */
    public function it_can_filter_tournament_groups_by_phase_type(): void
    {
        $season = Season::query()->create([
            'id' => 2025,
            'season_name' => '2025/2026',
        ]);

        $tier = TournamentTier::query()->create([
            'tier_name' => '1. division',
            'rank_level' => 2,
        ]);

        TournamentGroup::query()->create([
            'season_id' => $season->id,
            'tier_id' => $tier->id,
            'group_name' => 'Kvartfinaler',
            'phase_type' => TournamentPhaseType::PLAYOFF->value,
        ]);
        TournamentGroup::query()->create([
            'season_id' => $season->id,
            'tier_id' => $tier->id,
            'group_name' => 'Pulje 2',
            'phase_type' => TournamentPhaseType::REGULAR_SEASON->value,
        ]);

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($seasonId: Int!, $phaseType: String) {
                tournamentGroups(seasonId: $seasonId, phaseType: $phaseType) {
                    groupName
                    phaseType
                }
            }
        ', [
            'seasonId' => 2025,
            'phaseType' => TournamentPhaseType::PLAYOFF->value,
        ]);

        $response->assertJsonMissingPath('errors');
        $response->assertJsonCount(1, 'data.tournamentGroups');
        $response->assertJson([
            'data' => [
                'tournamentGroups' => [
                    [
                        'groupName' => 'Kvartfinaler',
                        'phaseType' => TournamentPhaseType::PLAYOFF->value,
                    ],
                ],
            ],
        ]);
    }
}
