<?php

declare(strict_types=1);

namespace Tests\Unit;

use FlyCompany\BadmintonPlayerAPI\BadmintonPlayerAPI;
use FlyCompany\BadmintonPlayerAPI\Models\PlayerRankingIterator;
use FlyCompany\BadmintonPlayerAPI\RankingPeriodType;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BadmintonPlayerRankingTest extends TestCase
{
    public function test_ranking_pair_is_deserialized_for_both_periods(): void
    {
        $cache = Cache::store('array');
        $client = new BadmintonPlayerAPI(new Client, $cache);
        $body = json_encode([
            'current' => ['versionDate' => '2026-09-28'],
            'previous' => ['versionDate' => '2026-09-27'],
        ], JSON_THROW_ON_ERROR);

        foreach ([
            [RankingPeriodType::CURRENT, '2026-09-28'],
            [RankingPeriodType::PREVIOUS, '2026-09-27'],
        ] as [$period, $date]) {
            // Each period has its own cache entry; no external API request is needed.
            $cache->put('badmintonplayer-api:player-ranking-'.md5($period->value.'-'.now()->format('Y-m-d')), $body);
            $ranking = $client->getPlayerRanking($period);

            $this->assertSame($date, $ranking->versionDate);
            $this->assertInstanceOf(PlayerRankingIterator::class, $ranking->playerRankings);
        }
    }
}
