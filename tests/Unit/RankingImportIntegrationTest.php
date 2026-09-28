<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Club;
use App\Models\Member;
use App\Models\Point;
use FlyCompany\BadmintonPlayer\Jobs\ImportMembers;
use FlyCompany\BadmintonPlayer\Jobs\ImportPoints;
use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use FlyCompany\Members\MemberManager;
use FlyCompany\Members\PointsManager;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RankingImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_and_point_jobs_import_saved_ranking_responses(): void
    {
        $current = file_get_contents(__DIR__.'/../Integration/rankingCurrentFull.json');
        $previous = file_get_contents(__DIR__.'/../Integration/rankingPreviousFull.json');
        $this->assertCount(100, json_decode($current, true, 512, JSON_THROW_ON_ERROR)['current']['playerRankings']);
        $this->assertCount(100, json_decode($previous, true, 512, JSON_THROW_ON_ERROR)['previous']['playerRankings']);

        // The ranking header and the first iterator page each request a response.
        $handler = new MockHandler([
            new Response(200, [], $current),
            new Response(200, [], $current),
            new Response(200, [], $previous),
            new Response(200, [], $previous),
        ]);
        $client = new BadmintonPlayerAPI(new Client(['handler' => HandlerStack::create($handler)]), Cache::store('array'));
        $club = Club::query()->create(['id' => 10007, 'badmintonPlayerId' => 10007, 'name1' => 'Fixture Club']);
        $pointsManager = new PointsManager;

        $this->assertSame(0, (new ImportMembers([$club->id]))->handle($client, new MemberManager, $pointsManager));

        $this->assertEqualsCanonicalizing(
            ['900101-19', '900101-31'],
            $club->members()->pluck('refId')->all()
        );
        $this->assertFalse(Member::query()->where('refId', '900101-19')->firstOrFail()->inactive);
        $this->assertTrue(Member::query()->where('refId', '900101-31')->firstOrFail()->inactive);

        $this->assertSame(0, (new ImportPoints($club->id, RankingPeriodType::CURRENT))->handle($client, $pointsManager));
        $this->assertSame(0, (new ImportPoints($club->id, RankingPeriodType::PREVIOUS))->handle($client, $pointsManager));

        $member = Member::query()->where('refId', '900101-19')->firstOrFail();
        foreach (['2026-09-02', '2026-08-02'] as $version) {
            $this->assertEqualsCanonicalizing(
                [2926, 3218, 3036],
                Point::query()->where('member_id', $member->id)->whereDate('version', $version)->pluck('points')->all()
            );
        }
        $this->assertSame(0, Point::query()->where('member_id', Member::query()->where('refId', '900101-31')->firstOrFail()->id)->count());
        $this->assertCount(2, $club->members);
        $this->assertCount(0, $handler);
    }
}
