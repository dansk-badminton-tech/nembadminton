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
 * Validation of a lineup with Reserve Players (§43): X from a lower Squad plays for Y on a higher Squad.
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
            betterReserve { refId name points }
          }
        }
    ';

    /** @test */
    public function an_order_breach_that_only_exists_with_the_reserve_in_place_is_reported(): void
    {
        // Hold 1: Anders (90) plays 1. HS, Y (100) plays 2. HS. Hold 2: X (150). X on 2. HS has more than Anders + 50.
        $anders = $this->men('Anders', 'HS', 90);
        $y = $this->men('Y', 'HS', 100);
        $x = $this->men('X', 'HS', 150);
        $hold1 = $this->squad([['1. HS', 'HS', [$anders]], ['2. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);

        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateSquads', []);

        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateSquads', [
            ['refId' => 'Anders', 'category' => 'HS', 'belowPlayer' => [['refId' => 'X']]],
        ]);
    }

    /** @test */
    public function an_order_breach_that_only_existed_with_the_replaced_player_is_not_reported(): void
    {
        // Y (200) stands below Anders (100) on Hold 1: a breach. X (100) replaces Y, so there is none.
        $anders = $this->men('Anders', 'HS', 100);
        $y = $this->men('Y', 'HS', 200);
        $x = $this->men('X', 'HS', 100);
        $hold1 = $this->squad([['1. HS', 'HS', [$anders]], ['2. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);

        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateSquads.0.refId', 'Anders');

        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateSquads', []);
    }

    /** @test */
    public function the_reserve_on_two_squads_is_never_a_cross_squad_breach(): void
    {
        $y = $this->men('Y', 'HS', 300);
        $x = $this->men('X', 'HS', 250);
        $hold1 = $this->squad([['1. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::CROSS_SQUADS_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateCrossSquads', []);
    }

    /** @test */
    public function a_cross_squad_breach_with_the_reserve_in_place_is_reported(): void
    {
        // Hold 1: Y (300). Hold 2: X (100), Bo (200). With X in Y's place, Bo on Hold 2 has more than X + 50 on Hold 1.
        $y = $this->men('Y', 'HS', 300);
        $x = $this->men('X', 'HS', 100);
        $bo = $this->men('Bo', 'HS', 200);
        $hold1 = $this->squad([['1. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$bo]], ['2. HS', 'HS', [$x]]]);

        $this->validate(self::CROSS_SQUADS_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateCrossSquads', []);

        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::CROSS_SQUADS_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateCrossSquads', [
            ['refId' => 'Bo', 'belowPlayer' => [['refId' => 'X', 'category' => 'HS']]],
        ]);
    }

    /** @test */
    public function a_reserve_stronger_than_the_replaced_player_is_a_strength_increase(): void
    {
        $y = $this->men('Y', 'HS', 100);
        $x = $this->men('X', 'HS', 120);
        $hold1 = $this->squad([['1. HS', 'HS', [$y]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', [[
            'type' => 'STRENGTH_INCREASE',
            'squadIndex' => 0,
            'category' => 'HS',
            'categoryName' => '1. HS',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 120],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 100],
            'betterReserve' => null,
        ]]);
    }

    /** @test */
    public function a_reserve_as_strong_as_the_replaced_player_is_fine(): void
    {
        $hold1 = $this->squad([['1. HS', 'HS', [$this->men('Y', 'HS', 100)]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$this->men('X', 'HS', 100)]]]);
        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', []);
    }

    /** @test */
    public function a_stronger_member_on_a_lower_squad_who_is_no_stronger_than_the_replaced_player_is_a_better_reserve(): void
    {
        // Y (200) on Hold 1. Hold 2: X (100). Hold 3: Bo (150) and Carl (190), both better than X and no better than Y;
        // Dan (250) is stronger than Y and Eva is a woman, so neither counts.
        $hold1 = $this->squad([['1. HS', 'HS', [$this->men('Y', 'HS', 200)]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$this->men('X', 'HS', 100)]]]);
        $hold3 = $this->squad([
            ['1. HS', 'HS', [$this->men('Dan', 'HS', 250)]],
            ['2. HS', 'HS', [$this->men('Bo', 'HS', 150)]],
            ['3. HS', 'HS', [$this->men('Carl', 'HS', 190)]],
            ['1. DS', 'DS', [$this->women('Eva', 'HS', 180)]],
        ]);
        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2, $hold3])->assertJsonPath('data.validateReserves', [[
            'type' => 'BETTER_RESERVE_AVAILABLE',
            'squadIndex' => 0,
            'category' => 'HS',
            'categoryName' => '1. HS',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 100],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 200],
            'betterReserve' => ['refId' => 'Carl', 'name' => 'Carl', 'points' => 190],
        ]]);
    }

    /** @test */
    public function a_member_who_is_a_reserve_themselves_is_not_a_better_reserve(): void
    {
        $hold1 = $this->squad([
            ['1. HS', 'HS', [$this->men('Y', 'HS', 200)]],
            ['2. HS', 'HS', [$this->men('W', 'HS', 200)]],
        ]);
        $hold2 = $this->squad([
            ['1. HS', 'HS', [$this->men('Z', 'HS', 150)]],
            ['2. HS', 'HS', [$this->men('X', 'HS', 100)]],
        ]);
        $hold1->reserves = [
            ['reserveRefId' => 'X', 'replacedRefId' => 'Y'],
            ['reserveRefId' => 'Z', 'replacedRefId' => 'W'],
        ];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', []);
    }

    /** @test */
    public function a_stale_record_changes_nothing(): void
    {
        // X stands on the same Squad as Y: the record doesn't count.
        $anders = $this->men('Anders', 'HS', 100);
        $y = $this->men('Y', 'HS', 200);
        $x = $this->men('X', 'HS', 300);
        $hold1 = $this->squad([['1. HS', 'HS', [$anders]], ['2. HS', 'HS', [$y]], ['3. HS', 'HS', [$x]]]);
        $withoutRecord = $this->validate(self::SQUAD_ORDER_QUERY, [$hold1])->json('data.validateSquads');
        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1])->assertJsonPath('data.validateReserves', []);
        $this->assertNotEmpty($withoutRecord);
        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1])->assertJsonPath('data.validateSquads', $withoutRecord);
    }

    /** @test */
    public function the_reserve_takes_every_slot_of_the_replaced_player(): void
    {
        // Y plays 1. HS and 1. HD on Hold 1. With X in place, the basic check still sees full categories.
        $y = $this->menWithPoints('Y', ['HS' => 100, 'HD' => 100]);
        $partner = $this->menWithPoints('Partner', ['HD' => 100]);
        $x = $this->menWithPoints('X', ['HS' => 90, 'HD' => 120]);
        $hold1 = $this->squad([['1. HS', 'HS', [$y]], ['1. HD', 'HD', [$y, $partner]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [['reserveRefId' => 'X', 'replacedRefId' => 'Y']];

        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', [[
            'type' => 'STRENGTH_INCREASE',
            'squadIndex' => 0,
            'category' => 'HD',
            'categoryName' => '1. HD',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 120],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 100],
            'betterReserve' => null,
        ]]);

        $this->validate(/** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) { index spotsFulfilled }
            }
        ', [$hold1, $hold2])->assertJsonPath('data.validateBasicSquads', [
            ['index' => 0, 'spotsFulfilled' => true],
            ['index' => 1, 'spotsFulfilled' => true],
        ]);
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

    private function women(string $name, string $category, int $points): Player
    {
        return $this->player($name, 'WOMEN', [$category => $points]);
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
