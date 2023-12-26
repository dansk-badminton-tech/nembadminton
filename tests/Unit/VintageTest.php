<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use FlyCompany\BadmintonPlayerAPI\Vintage;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class VintageTest extends TestCase
{

    use CreatesApplication;

    /**
     * @test
     * @return void
     */
    public function calculateU19(): void
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d', '2022-03-15'));
        $vintage = Util::calculateVintage(Carbon::createFromFormat('Y-m-d', '2003-02-21'));
        $this->assertEquals(Vintage::U19, $vintage);
        $vintage = Util::calculateVintage(Carbon::createFromFormat('Y-m-d', '2004-06-21'));
        $this->assertEquals(Vintage::U19, $vintage);
        Carbon::setTestNow();
    }

    /**
     * @test
     * @return void
     */
    public function calculateU17(): void
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d', '2022-03-15'));
        $vintage = Util::calculateVintage(Carbon::createFromFormat('Y-m-d', '2005-02-21'));
        $this->assertEquals(Vintage::U17, $vintage);
        $vintage = Util::calculateVintage(Carbon::createFromFormat('Y-m-d', '2006-08-21'));
        $this->assertEquals(Vintage::U17, $vintage);
        Carbon::setTestNow();
    }

    /**
     * @test
     * @return void
     */
    public function calculateU15(): void
    {
        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d', '2022-03-15'));
        $vintage = Util::calculateVintage(Carbon::createFromFormat('Y-m-d', '2007-02-21'));
        $this->assertEquals(Vintage::U15, $vintage);
        $vintage = Util::calculateVintage(Carbon::createFromFormat('Y-m-d', '2008-08-21'));
        $this->assertEquals(Vintage::U15, $vintage);
        Carbon::setTestNow();
    }
}
