<?php
declare(strict_types=1);

namespace Tests\Unit;

use FlyCompany\Scraper\Parser;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ParserTest extends TestCase
{
    /**
     * @test
     */
    public function it_parses_team_fights_with_multiple_rounds()
    {
        $html = <<<HTML
<table class="matchlist">
    <tr><th>Header</th></tr>
    <tr class="roundheader">
        <td>7 el. 8 13-03-2027</td>
    </tr>
    <tr class="grouprow">
        <td class="team">Team A</td>
        <td class="team">Team B</td>
        <td class="matchno">123</td>
        <td class="time">10:00</td>
    </tr>
</table>
HTML;

        $parser = new Parser();
        $results = $parser->teamFights($html);

        $this->assertCount(1, $results);
        $this->assertEquals(7, $results[0]['round']);
        $this->assertInstanceOf(Carbon::class, $results[0]['roundDate']);
        $this->assertEquals('2027-03-13', $results[0]['roundDate']->format('Y-m-d'));
    }

    /**
     * @test
     */
    public function it_parses_team_fights_with_single_round()
    {
        $html = <<<HTML
<table class="matchlist">
    <tr><th>Header</th></tr>
    <tr class="roundheader">
        <td>1 01-01-2024</td>
    </tr>
    <tr class="grouprow">
        <td class="team">Team A</td>
        <td class="team">Team B</td>
        <td class="matchno">123</td>
        <td class="time">10:00</td>
    </tr>
</table>
HTML;

        $parser = new Parser();
        $results = $parser->teamFights($html);

        $this->assertCount(1, $results);
        $this->assertEquals(1, $results[0]['round']);
        $this->assertInstanceOf(Carbon::class, $results[0]['roundDate']);
        $this->assertEquals('2024-01-01', $results[0]['roundDate']->format('Y-m-d'));
    }
}
