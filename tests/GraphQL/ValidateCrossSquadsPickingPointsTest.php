<?php

namespace Tests\GraphQL;

use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * §38 stk. 4: the TeamRound's ranking decides which Squad a player may stand on, also for a Squad with its own ranking.
 * The client sends those points as pickingPoints; points stay the Squad's own (stk. 2-3).
 */
class ValidateCrossSquadsPickingPointsTest extends TestCase
{
    use MakesGraphQLRequests;

    private const CROSS_SQUADS = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!) {
          validateCrossSquads(input: $input) {
            refId
            belowPlayer { refId category balance }
          }
        }
    ';

    private const SQUADS = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!) {
          validateSquads(input: $input) {
            refId
            category
          }
        }
    ';

    private static function hsPoints(int $points, string $version): array
    {
        return [['category' => 'HS', 'points' => $points, 'position' => 1, 'version' => $version, 'vintage' => 'SEN']];
    }

    private static function player(string $refId, string $name, array $points, ?array $pickingPoints = null): array
    {
        $player = ['refId' => $refId, 'name' => $name, 'gender' => 'MEN', 'points' => $points];
        if ($pickingPoints !== null) {
            $player['pickingPoints'] = $pickingPoints;
        }

        return $player;
    }

    private static function team(string $name, array ...$players): array
    {
        return [
            'name' => $name,
            'squad' => [
                'playerLimit' => 10,
                'categories' => array_map(static fn (array $player, int $index) => [
                    'category' => 'HS',
                    'name' => ($index + 1).'. HS',
                    'players' => [$player],
                ], $players, array_keys($players)),
            ],
        ];
    }

    /**
     * Hold 1 plays on the TeamRound's February ranking: Anders 2000. Hold 2 has its own March ranking.
     */
    private function crossSquads(array $bo): array
    {
        $anders = self::player('010190-1111', 'Anders', self::hsPoints(2000, '2026-02-01'));

        return $this->graphQL(self::CROSS_SQUADS, ['input' => [
            self::team('Hold 1', $anders),
            self::team('Hold 2', $bo),
        ]])->json('data.validateCrossSquads');
    }

    #[Test]
    public function legalOnTheTeamRoundsRankingIsValidThoughOverTheLimitOnTheSquadsOwn(): void
    {
        $bo = self::player('020290-2222', 'Bo', self::hsPoints(2060, '2026-03-01'), self::hsPoints(1940, '2026-02-01'));

        $this->assertSame([], $this->crossSquads($bo));
    }

    #[Test]
    public function overTheLimitOnTheTeamRoundsRankingIsFoundThoughLegalOnTheSquadsOwn(): void
    {
        $bo = self::player('020290-2222', 'Bo', self::hsPoints(1940, '2026-03-01'), self::hsPoints(2060, '2026-02-01'));

        $this->assertSame([[
            'refId' => '020290-2222',
            'belowPlayer' => [['refId' => '010190-1111', 'category' => 'HS', 'balance' => 60]],
        ]], $this->crossSquads($bo));
    }

    #[Test]
    public function withoutAnOwnRankingPickingPointsEqualToPointsGiveTheSameResult(): void
    {
        $over = self::hsPoints(2060, '2026-02-01');
        $legal = self::hsPoints(2040, '2026-02-01');

        $this->assertSame(
            $this->crossSquads(self::player('020290-2222', 'Bo', $over)),
            $this->crossSquads(self::player('020290-2222', 'Bo', $over, $over)),
        );
        $this->assertSame(
            $this->crossSquads(self::player('020290-2222', 'Bo', $legal)),
            $this->crossSquads(self::player('020290-2222', 'Bo', $legal, $legal)),
        );
    }

    #[Test]
    public function inputWithoutPickingPointsFromAnOlderClientComparesPoints(): void
    {
        $result = $this->crossSquads(self::player('020290-2222', 'Bo', self::hsPoints(2060, '2026-03-01')));

        $this->assertSame([[
            'refId' => '020290-2222',
            'belowPlayer' => [['refId' => '010190-1111', 'category' => 'HS', 'balance' => 60]],
        ]], $result);
    }

    #[Test]
    public function theOrderOnASquadWithItsOwnRankingUsesItsOwnPoints(): void
    {
        // On the Squad's March ranking Carl (2000) stands above Dan (2100); on February it would be fine.
        $carl = self::player('030390-3333', 'Carl', self::hsPoints(2000, '2026-03-01'), self::hsPoints(2200, '2026-02-01'));
        $dan = self::player('040490-4444', 'Dan', self::hsPoints(2100, '2026-03-01'), self::hsPoints(2100, '2026-02-01'));

        $result = $this->graphQL(self::SQUADS, ['input' => [self::team('Hold 2', $carl, $dan)]])
            ->json('data.validateSquads');

        $this->assertSame([['refId' => '030390-3333', 'category' => 'HS']], $result);
    }
}
