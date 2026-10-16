<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
use App\Models\SquadReserve;
use App\Models\TeamRound;
use FlyCompany\TeamFight\Export\Exporter;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class ExporterTest extends TestCase
{
    /**
     * @test
     */
    public function it_exports_csv_with_categories_by_default(): void
    {
        $team = new TeamRound();
        $squad = new Squad();
        $category = new SquadCategory(['name' => '1. HS', 'category' => 'HS']);
        $player = new SquadMember(['name' => 'John Doe']);

        $category->setRelation('players', new Collection([$player]));
        $squad->setRelation('categories', new Collection([$category]));
        $team->setRelation('squads', new Collection([$squad]));

        $exporter = new Exporter();
        $csv = $exporter->exportToCSV($team);

        $expected = '"Hold 1"' . PHP_EOL . '"1. HS","John Doe"' . PHP_EOL . '';
        $this->assertSame($expected, $csv);
    }

    /**
     * @test
     */
    public function it_exports_csv_without_categories_when_excluded(): void
    {
        $team = new TeamRound();
        $squad = new Squad();
        $category = new SquadCategory(['name' => '1. HS', 'category' => 'HS']);
        $player1 = new SquadMember(['name' => 'John Doe']);
        $player2 = new SquadMember(['name' => 'Jane Smith']);

        $category->setRelation('players', new Collection([$player1, $player2]));
        $squad->setRelation('categories', new Collection([$category]));
        $team->setRelation('squads', new Collection([$squad]));

        $exporter = new Exporter();
        $csv = $exporter->exportToCSV($team, false);

        $expected = '"Hold 1"' . PHP_EOL . '"John Doe"' . PHP_EOL . '"Jane Smith"' . PHP_EOL . '';
        $this->assertSame($expected, $csv);
    }

    /**
     * @test
     */
    public function it_exports_the_reserves_of_the_official_lineup_after_each_squads_categories(): void
    {
        $team = $this->teamWithReserves();

        $expected = '"Hold 1"'.PHP_EOL
            .'"1. DS","Yrsa"'.PHP_EOL
            .'"2. DS","Vera"'.PHP_EOL
            .'"Reserve","Xenia for Yrsa"'.PHP_EOL
            .',"Zara for Vera"'.PHP_EOL
            .'';
        $this->assertSame($expected, (new Exporter)->exportToCSV($team));
    }

    /**
     * @test
     */
    public function it_exports_the_reserves_without_categories(): void
    {
        $team = $this->teamWithReserves();

        $expected = '"Hold 1"'.PHP_EOL
            .'"Yrsa"'.PHP_EOL
            .'"Vera"'.PHP_EOL
            .'"Xenia for Yrsa (reserve)"'.PHP_EOL
            .'"Zara for Vera (reserve)"'.PHP_EOL
            .'';
        $this->assertSame($expected, (new Exporter)->exportToCSV($team, false));
    }

    /**
     * Hold 1 with two reserves in the official lineup, and one in a draft Scenario that isn't exported.
     */
    private function teamWithReserves(): TeamRound
    {
        $team = new TeamRound;
        $squad = new Squad;
        $ds1 = new SquadCategory(['name' => '1. DS', 'category' => 'DS']);
        $ds1->setRelation('players', new Collection([new SquadMember(['name' => 'Yrsa'])]));
        $ds2 = new SquadCategory(['name' => '2. DS', 'category' => 'DS']);
        $ds2->setRelation('players', new Collection([new SquadMember(['name' => 'Vera'])]));
        $squad->setRelation('categories', new Collection([$ds1, $ds2]));
        $squad->setRelation('reserves', new Collection([
            new SquadReserve(['reserve_name' => 'Xenia', 'replaced_name' => 'Yrsa']),
            new SquadReserve(['reserve_name' => 'Una', 'replaced_name' => 'Yrsa', 'team_round_scenario_id' => 7]),
            new SquadReserve(['reserve_name' => 'Zara', 'replaced_name' => 'Vera']),
        ]));
        $team->setRelation('squads', new Collection([$squad]));

        return $team;
    }

    /**
     * @test
     */
    public function it_removes_duplicate_players_within_squad_when_excluding_categories(): void
    {
        $team = new TeamRound();
        $squad = new Squad();
        $category1 = new SquadCategory(['name' => '1. HS', 'category' => 'HS']);
        $category2 = new SquadCategory(['name' => '1. HD', 'category' => 'HD']);
        $player1 = new SquadMember(['name' => 'John Doe']);
        $player1Duplicate = new SquadMember(['name' => 'John Doe']);
        $player2 = new SquadMember(['name' => 'Jane Smith']);

        $category1->setRelation('players', new Collection([$player1]));
        $category2->setRelation('players', new Collection([$player1Duplicate, $player2]));
        $squad->setRelation('categories', new Collection([$category1, $category2]));
        $team->setRelation('squads', new Collection([$squad]));

        $exporter = new Exporter();
        $csv = $exporter->exportToCSV($team, false);

        $expected = '"Hold 1"' . PHP_EOL . '"John Doe"' . PHP_EOL . '"Jane Smith"' . PHP_EOL . '';
        $this->assertSame($expected, $csv);
    }
}
