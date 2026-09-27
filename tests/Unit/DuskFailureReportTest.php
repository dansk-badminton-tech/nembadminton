<?php

namespace Tests\Unit;

use App\Console\DuskFailureReport;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class DuskFailureReportTest extends TestCase
{
    public function test_it_selects_only_failed_and_errored_methods(): void
    {
        $xml = <<<'XML'
<testsuites><testsuite tests="3">
  <testcase class="Tests\Browser\PassingTest" name="test_ok"/>
  <testcase class="Tests\Browser\FailingTest" name="test_failure"><failure/></testcase>
  <testcase class="Tests\Browser\ErrorTest" name="test_error"><error/></testcase>
</testsuite></testsuites>
XML;

        $filter = DuskFailureReport::failedTestFilter($xml);

        $this->assertSame(1, preg_match($filter, 'Tests\Browser\FailingTest::test_failure'));
        $this->assertSame(1, preg_match($filter, 'Tests\Browser\ErrorTest::test_error'));
        $this->assertSame(0, preg_match($filter, 'Tests\Browser\PassingTest::test_ok'));
    }

    public function test_it_does_not_retry_when_every_test_passed(): void
    {
        $xml = '<testsuites><testsuite tests="1"><testcase class="Tests\Browser\PassingTest" name="test_ok"/></testsuite></testsuites>';

        $this->assertNull(DuskFailureReport::failedTestFilter($xml));
    }

    public function test_it_rejects_an_incomplete_report(): void
    {
        $xml = '<testsuites><testsuite tests="2"><testcase class="Tests\Browser\PassingTest" name="test_ok"/></testsuite></testsuites>';

        $this->expectException(UnexpectedValueException::class);
        DuskFailureReport::failedTestFilter($xml);
    }
}
