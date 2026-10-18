<?php

declare(strict_types=1);

namespace Tests\Integration;

use Carbon\Carbon;
use FlyCompany\Scraper\BadmintonPlayer;
use FlyCompany\Scraper\BadmintonPlayerHelper;
use FlyCompany\Scraper\Parser;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\CreatesApplication;

class BadmintonPlayerTest extends TestCase
{

    use CreatesApplication;

    /**
     * @return void
     * @throws \JsonException
     */
    #[Test]
    public function getVersions(): void
    {
        $badmintonPlayer = new BadmintonPlayer(new Parser(), Cache::store());
        $versions = $badmintonPlayer->getVersions(2021);
        $this->assertSame(7, $versions[0]->month);
    }

    /**
     * @return void
     * @throws \JsonException
     */
    #[Test]
    public function getRankingVersions(): void
    {
        $badmintonPlayer = new BadmintonPlayer(new Parser(), Cache::store());
        $versions = $badmintonPlayer->getVersions(2021);
        $rankingMonths = BadmintonPlayerHelper::filterToRankingMonths($versions);
        $this->assertSame(7, $rankingMonths->shift()->month);
    }

}
