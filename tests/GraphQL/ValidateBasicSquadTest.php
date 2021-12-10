<?php

namespace Tests\GraphQL;

use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\CategoryFactory;
use Tests\CreatesApplication;

class ValidateBasicSquadTest extends BaseTestCase
{

    use CreatesApplication;
    use MakesGraphQLRequests;

    /**
     * @test
     * @throws \JsonException
     */
    public function validSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                spotsFulfilled
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'spotsFulfilled' => true
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function invalidSquadInMD(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                spotsFulfilled
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'spotsFulfilled' => false
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function invalidSquadInDD(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                spotsFulfilled
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'spotsFulfilled' => false
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function oneInvalidSquadAndOneValidSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $team1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-14');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-19');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-20');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $team2 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$team1, $team2], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                spotsFulfilled
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'spotsFulfilled' => true
                        ],
                        [
                            'index' => 1,
                            'spotsFulfilled' => false
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function oneInvalidSquadAndTwoValidSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $team1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-14');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-19');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-20');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $team2 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-21');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-22');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-23');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-24');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-25');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-26');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-27');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-28');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-29');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-30');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $team3 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$team1, $team2, $team3], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                spotsFulfilled
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'spotsFulfilled' => true
                        ],
                        [
                            'index' => 1,
                            'spotsFulfilled' => false
                        ],
                        [
                            'index' => 2,
                            'spotsFulfilled' => true
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playerOnlyInOneCategory(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');
        $women5 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women5, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                spotsFulfilled
                missingPlayerInCategory
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'spotsFulfilled' => true,
                            'missingPlayerInCategory' => true
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function validateSquadNoMissingPlayerInCategory(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateBasicSquads(input: $input) {
                index
                missingPlayerInCategory
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateBasicSquads' => [
                        [
                            'index' => 0,
                            'missingPlayerInCategory' => false
                        ]
                    ]
                ]
            ]);
    }
}
