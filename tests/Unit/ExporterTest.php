<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Squad;
use App\Models\SquadCategory;
use App\Models\SquadMember;
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
}
