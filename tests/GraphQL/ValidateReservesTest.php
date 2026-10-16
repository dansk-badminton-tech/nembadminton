<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Point;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * Validation of a lineup with Reserve Players (§43): X, an Available Member or a Member on a lower Squad, plays for Y on a
 * Squad. Only §43 stk. 3 looks at the records.
 */
class ValidateReservesTest extends TestCase
{
    use MakesGraphQLRequests;

    private const SQUAD_ORDER_QUERY = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!) {
          validateSquads(input: $input) {
            refId
            category
            belowPlayer { refId }
          }
        }
    ';

    private const CROSS_SQUADS_QUERY = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!) {
          validateCrossSquads(input: $input) {
            refId
            belowPlayer { refId category }
          }
        }
    ';

    private const RESERVES_QUERY = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!) {
          validateReserves(input: $input) {
            type
            squadIndex
            category
            categoryName
            reserve { refId name points }
            replaced { refId name points }
          }
        }
    ';

    /** @test */
    public function the_order_checks_validate_the_lineup_as_placed(): void
    {
        // Y (200) stands below Anders (100) on Hold 1: a breach. A record "X for Y" doesn't change that, because §43
        // stk. 3 measures the reserve against the original lineup.
        $anders = $this->men('Anders', 'HS', 100);
        $y = $this->men('Y', 'HS', 200);
        $x = $this->men('X', 'HS', 100);
        $hold1 = $this->squad([['1. HS', 'HS', [$anders]], ['2. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $withoutRecord = $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->json('data.validateSquads');
        $crossWithoutRecord = $this->validate(self::CROSS_SQUADS_QUERY, [$hold1, $hold2])->json('data.validateCrossSquads');

        $hold1->reserves = [['reserve' => $x, 'replacedRefId' => 'Y']];

        $this->assertNotEmpty($withoutRecord);
        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateSquads', $withoutRecord);
        $this->validate(self::CROSS_SQUADS_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateCrossSquads', $crossWithoutRecord);
    }

    /** @test */
    public function a_reserve_stronger_than_the_replaced_player_is_a_strength_increase(): void
    {
        $y = $this->men('Y', 'HS', 100);
        $x = $this->men('X', 'HS', 120);
        $hold1 = $this->squad([['1. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserve' => $x, 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', [[
            'type' => 'STRENGTH_INCREASE',
            'squadIndex' => 0,
            'category' => 'HS',
            'categoryName' => '1. HS',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 120],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 100],
        ]]);
    }

    /** @test */
    public function an_available_member_is_checked_with_the_points_in_the_record(): void
    {
        // X isn't placed on any Squad; the record carries their points.
        $hold1 = $this->squad([['1. HS', 'HS', [$this->men('Y', 'HS', 100)]]]);
        $hold1->reserves = [['reserve' => $this->men('X', 'HS', 130), 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1])
            ->assertJsonPath('data.validateReserves.0.reserve', ['refId' => 'X', 'name' => 'X', 'points' => 130]);
    }

    /** @test */
    public function a_reserve_as_strong_as_the_replaced_player_is_fine(): void
    {
        $x = $this->men('X', 'HS', 100);
        $hold1 = $this->squad([['1. HS', 'HS', [$this->men('Y', 'HS', 100)]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserve' => $x, 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', []);
    }

    /** @test */
    public function a_stale_record_changes_nothing(): void
    {
        // X stands on the same Squad as Y: the record doesn't count.
        $x = $this->men('X', 'HS', 300);
        $hold1 = $this->squad([['1. HS', 'HS', [$this->men('Y', 'HS', 200)]], ['2. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserve' => $x, 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1])->assertJsonPath('data.validateReserves', []);
    }

    /** @test */
    public function the_reserve_is_checked_in_every_category_of_the_replaced_player(): void
    {
        // Y plays 1. HS and 1. HD on Hold 1. X is weaker in HS and stronger in HD.
        $y = $this->menWithPoints('Y', ['HS' => 100, 'HD' => 100]);
        $partner = $this->menWithPoints('Partner', ['HD' => 100]);
        $x = $this->menWithPoints('X', ['HS' => 90, 'HD' => 120]);
        $hold1 = $this->squad([['1. HS', 'HS', [$y]], ['1. HD', 'HD', [$y, $partner]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserve' => $x, 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', [[
            'type' => 'STRENGTH_INCREASE',
            'squadIndex' => 0,
            'category' => 'HD',
            'categoryName' => '1. HD',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 120],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 100],
        ]]);
    }

    /**
     * @param  list<array{0: string, 1: string, 2: list<Player>}>  $categories  [name, category, players]
     */
    private function squad(array $categories): Squad
    {
        $squad = new Squad;
        $squad->playerLimit = 10;
        foreach ($categories as [$name, $type, $players]) {
            $category = new Category;
            $category->name = $name;
            $category->category = $type;
            $category->players = $players;
            $squad->categories[] = $category;
        }

        return $squad;
    }

    private function men(string $name, string $category, int $points): Player
    {
        return $this->player($name, 'MEN', [$category => $points]);
    }

    /**
     * @param  array<string, int>  $points
     */
    private function menWithPoints(string $name, array $points): Player
    {
        return $this->player($name, 'MEN', $points);
    }

    /**
     * The refId is the name, so findings are easy to read.
     *
     * @param  array<string, int>  $pointsByCategory
     */
    private function player(string $name, string $gender, array $pointsByCategory): Player
    {
        $player = new Player;
        $player->name = $name;
        $player->refId = $name;
        $player->gender = $gender;
        foreach ($pointsByCategory as $category => $points) {
            $point = new Point;
            $point->category = $category;
            $point->points = $points;
            $point->position = 1;
            $point->version = '2026-08-01';
            $point->vintage = 'SEN';
            $player->points[] = $point;
        }

        return $player;
    }

    /**
     * @param  list<Squad>  $squads
     */
    private function validate(string $query, array $squads): \Illuminate\Testing\TestResponse
    {
        $input = array_map(fn (Squad $squad) => ['name' => 'Team', 'squad' => $squad], $squads);
        $json = SerializerHelper::getSerializer()->encode($input, 'json');

        return $this->graphQL($query, ['input' => json_decode($json, true, 512, JSON_THROW_ON_ERROR)])->assertJsonMissingPath('errors');
    }
}
