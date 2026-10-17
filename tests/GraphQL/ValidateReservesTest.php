<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use FlyCompany\TeamFight\Models\Category;
use FlyCompany\TeamFight\Models\Player;
use FlyCompany\TeamFight\Models\Point;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Validation of a lineup with Reserve Players (§43): X stands in the Squad's slots as played, instead of Y. The normal
 * checks validate that lineup; only the §43 stk. 3 warning looks at the records.
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

    #[Test]
    public function the_lineup_as_played_passes_the_normal_checks(): void
    {
        // Lin Dan (1. HS on Hold 1) is ill. Daniel from Hold 2 comes in, Jesper moves up to 1. HS and Daniel takes 2. HS.
        // Daniel keeps his slot on Hold 2 and is never compared with himself.
        $jesper = $this->men('Jesper', 'HS', 400);
        $daniel = $this->men('Daniel', 'HS', 300);
        $hold1 = $this->squad([['1. HS', 'HS', [$jesper]], ['2. HS', 'HS', [$daniel]]]);
        $hold2 = $this->squad([['1. HS', 'HS', [$daniel]]]);
        $hold1->reserves = [$this->record('Daniel', $this->men('Lin Dan', 'HS', 900))];

        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateSquads', []);
        $this->validate(self::CROSS_SQUADS_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateCrossSquads', []);
        $this->validate(self::RESERVES_QUERY, [$hold1, $hold2])->assertJsonPath('data.validateReserves', []);
    }

    #[Test]
    public function a_reserve_in_the_wrong_order_breaks_the_order_check(): void
    {
        // Daniel left in Lin Dan's 1. HS, above the stronger Jesper.
        $daniel = $this->men('Daniel', 'HS', 300);
        $hold1 = $this->squad([['1. HS', 'HS', [$daniel]], ['2. HS', 'HS', [$this->men('Jesper', 'HS', 400)]]]);
        $hold1->reserves = [$this->record('Daniel', $this->men('Lin Dan', 'HS', 900))];

        $this->validate(self::SQUAD_ORDER_QUERY, [$hold1])->assertJsonPath('data.validateSquads.0.refId', 'Daniel');
    }

    #[Test]
    public function a_reserve_stronger_than_the_replaced_player_is_a_strength_increase(): void
    {
        $x = $this->men('X', 'HS', 120);
        $hold1 = $this->squad([['1. HS', 'HS', [$x]]]);
        $hold1->reserves = [$this->record('X', $this->men('Y', 'HS', 100))];

        $this->validate(self::RESERVES_QUERY, [$hold1])->assertJsonPath('data.validateReserves', [[
            'type' => 'STRENGTH_INCREASE',
            'squadIndex' => 0,
            'category' => 'HS',
            'categoryName' => '1. HS',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 120],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 100],
        ]]);
    }

    #[Test]
    public function a_reserve_as_strong_as_the_replaced_player_is_fine(): void
    {
        $hold1 = $this->squad([['1. HS', 'HS', [$this->men('X', 'HS', 100)]]]);
        $hold1->reserves = [$this->record('X', $this->men('Y', 'HS', 100))];

        $this->validate(self::RESERVES_QUERY, [$hold1])->assertJsonPath('data.validateReserves', []);
    }

    #[Test]
    public function a_stale_record_changes_nothing(): void
    {
        // Y still stands on the Squad, or X doesn't: the record doesn't count.
        $x = $this->men('X', 'HS', 300);
        $y = $this->men('Y', 'HS', 200);
        $both = $this->squad([['1. HS', 'HS', [$x]], ['2. HS', 'HS', [$y]]]);
        $both->reserves = [$this->record('X', $y)];
        $neither = $this->squad([['1. HS', 'HS', [$this->men('Z', 'HS', 100)]]]);
        $neither->reserves = [$this->record('X', $y)];

        $this->validate(self::RESERVES_QUERY, [$both, $neither])->assertJsonPath('data.validateReserves', []);
    }

    #[Test]
    public function the_reserve_is_checked_in_every_category_they_play_on_the_squad(): void
    {
        // X plays 1. HS and 1. HD on Hold 1 for Y. X is weaker in HS and stronger in HD.
        $x = $this->menWithPoints('X', ['HS' => 90, 'HD' => 120]);
        $partner = $this->menWithPoints('Partner', ['HD' => 100]);
        $hold1 = $this->squad([['1. HS', 'HS', [$x]], ['1. HD', 'HD', [$x, $partner]]]);
        $hold1->reserves = [$this->record('X', $this->menWithPoints('Y', ['HS' => 100, 'HD' => 100]))];

        $this->validate(self::RESERVES_QUERY, [$hold1])->assertJsonPath('data.validateReserves', [[
            'type' => 'STRENGTH_INCREASE',
            'squadIndex' => 0,
            'category' => 'HD',
            'categoryName' => '1. HD',
            'reserve' => ['refId' => 'X', 'name' => 'X', 'points' => 120],
            'replaced' => ['refId' => 'Y', 'name' => 'Y', 'points' => 100],
        ]]);
    }

    /**
     * @return array{reserveRefId: string, replaced: Player}
     */
    private function record(string $reserveRefId, Player $replaced): array
    {
        return ['reserveRefId' => $reserveRefId, 'replaced' => $replaced];
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
