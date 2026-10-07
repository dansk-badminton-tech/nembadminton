<?php

declare(strict_types=1);

namespace Tests\Unit;

use Carbon\Carbon;
use FlyCompany\BadmintonPlayerAPI\Util;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * §31 stk. 1: A player must turn 15 no later than 31.12. in the calendar year the season starts.
 */
class TooYoungForSeniorTest extends TestCase
{
    public static function birthdays(): array
    {
        return [
            'turns 15 on 31.12. of the start year' => ['2011-12-31', false],
            'turns 15 on 1.1. after the start year' => ['2012-01-01', true],
            'turns 15 mid next year' => ['2012-06-15', true],
            'adult' => ['1995-01-01', false],
            'turns 15 on 1.1. of the start year' => ['2011-01-01', false],
        ];
    }

    #[Test]
    #[DataProvider('birthdays')]
    public function itComparesBirthYearWithSeasonStartYear(string $birthday, bool $tooYoung): void
    {
        $this->assertSame($tooYoung, Util::isTooYoungForSenior(Carbon::parse($birthday), 2026));
    }

    #[Test]
    public function itJudgesByTheGivenSeasonNotToday(): void
    {
        Carbon::withTestNow(Carbon::create(2026, 10, 1), function () {
            $this->assertFalse(Util::isTooYoungForSenior(Carbon::parse('2012-01-01'), 2027));
        });
    }

    #[Test]
    public function itReadsTheBirthdayFromTheRefId(): void
    {
        $this->assertTrue(Util::isTooYoungForSeniorByRefId('120101-01', 2026));
        $this->assertFalse(Util::isTooYoungForSeniorByRefId('111231-01', 2026));
        $this->assertFalse(Util::isTooYoungForSeniorByRefId('950101-01', 2026));
    }
}
