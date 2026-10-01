<?php

namespace Tests\GraphQL;

use App\Models\Club;
use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Point;
use App\Models\User;
use Carbon\Carbon;
use FlyCompany\Members\PointsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class RankingVersionsTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $clubhouse = Clubhouse::factory()->create();
        $club = Club::query()->create(['id' => 10007, 'badmintonPlayerId' => 10007, 'name1' => 'Fixture Club']);
        $clubhouse->clubs()->attach($club);
        $this->member = Member::query()->create([
            'refId' => '9001011234',
            'name' => 'John Doe',
            'gender' => 'M',
            'birthday' => '1990-01-01',
            'playable' => true,
            'inactive' => false,
        ]);
        $this->member->clubs()->attach($club);

        $this->actingAs(User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'primary_role_id' => null,
        ]), 'api');
    }

    /** @test */
    public function the_most_recently_imported_version_of_a_republished_month_is_the_newest(): void
    {
        $this->addPoint('2026-08-01', '2026-08-01 06:00:00');
        // September was first published as 2026-09-02, then re-published as 2026-09-01.
        $this->addPoint('2026-09-02', '2026-09-01 06:31:08');
        $this->addPoint('2026-09-01', '2026-09-30 06:36:28');

        $this->graphQL(/** @lang GraphQL */ '{ rankingVersions newestRankingVersions latestRankingVersion }')
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('data.rankingVersions', ['2026-09-02', '2026-09-01', '2026-08-01'])
            ->assertJsonPath('data.newestRankingVersions', ['2026-09-01', '2026-08-01'])
            ->assertJsonPath('data.latestRankingVersion', '2026-09-01');
    }

    /** @test */
    public function reimporting_an_unchanged_version_makes_it_the_newest_again(): void
    {
        $this->addPoint('2026-09-02', '2026-09-01 06:31:08');
        $this->addPoint('2026-09-01', '2026-09-30 06:36:28');

        $this->travelTo(Carbon::parse('2026-10-01 06:00:00'));
        app(PointsManager::class)->addPointsByRefId('9001011234', 2730, 1, Carbon::parse('2026-09-02'), 'HS');

        $this->graphQL(/** @lang GraphQL */ '{ newestRankingVersions }')
            ->assertJsonPath('data.newestRankingVersions', ['2026-09-02']);
    }

    private function addPoint(string $version, string $updatedAt): void
    {
        Point::query()->insert([
            'points' => 2730,
            'position' => 1,
            'category' => 'HS',
            'vintage' => 'SEN',
            'member_id' => $this->member->id,
            'version' => $version,
            'created_at' => $updatedAt,
            'updated_at' => $updatedAt,
        ]);
    }
}
