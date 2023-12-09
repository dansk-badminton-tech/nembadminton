<?php

namespace Tests\GraphQL;

use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\CategoryFactory;
use Tests\CreatesApplication;

class ValidateSquadTest extends BaseTestCase
{

    use CreatesApplication;
    use MakesGraphQLRequests;

    /**
     * @test
     */
    public function useCase1()
    {
        // abc Aalborg - 2021-10-30
        $data = require __DIR__.'/SquadsUseCases/usecase1.php';

        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $data
        )->assertExactJson([
            'data' => [
                'validateSquads' => [
                    ["category"=>"HD","gender"=>"M","refId"=>"020202-1011"],
                    ["category"=>"HD","gender"=>"M","refId"=>"030610-05"],
                    ["category"=>"DD","gender"=>"K","refId"=>"040201-01"],
                    ["category"=>"DD","gender"=>"K","refId"=>"970512-21"]
                ]
            ],
            'extensions' => [
                'lighthouse_subscriptions' => [
                    'channel' => null
                ]
            ]
        ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMixValidateSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 101, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 50, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 250, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 200, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 150, 'HD', 150, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 100, 'HD', 100, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-9');
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
              validateSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => [
                        [
                            'refId' => $women1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $men1->refId,
                            'category' => 'MD',
                            'gender' => 'M'
                        ]
                    ]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMenSingleValidateSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 50, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 250, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 200, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 150, 'HD', 150, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 100, 'HD', 100, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 201, 'HD', 0, 0, '101010-10');

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
              validateSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => [
                        [
                            'refId' => $men3->refId,
                            'category' => 'HS',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men4->refId,
                            'category' => 'HS',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men5->refId,
                            'category' => 'HS',
                            'gender' => 'M'
                        ],
                    ]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMensDoubleValidateSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 51, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 51, 0, '101010-10');

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
              validateSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => [
                        [
                            'refId' => $men1->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men2->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men3->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men4->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                    ]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMensDoubleAndMixDoubleValidateSquad(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 101, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 51, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 51, 0, '101010-10');

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
              validateSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => [
                        [
                            'refId' => $women1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $men1->refId,
                            'category' => 'MD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men1->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men2->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men3->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                        [
                            'refId' => $men4->refId,
                            'category' => 'HD',
                            'gender' => 'M'
                        ],
                    ]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function validatingSquadWithoutMDAndDSAndDD(): void
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 101, 'DD', 0, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 50, 0, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 250, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 200, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 150, 'HD', 150, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 100, 'HD', 100, 0, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
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
              validateSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
             ->assertExactJson([
                 'data' => [
                     'validateSquads' => [
                         [
                             'refId' => $women1->refId,
                             'category' => 'MD',
                             'gender' => 'K'
                         ],
                         [
                             'refId' => $men1->refId,
                             'category' => 'MD',
                             'gender' => 'M'
                         ]
                     ]
                 ],
                 'extensions' => [
                     'lighthouse_subscriptions' => [
                         'channel' => null
                     ]
                 ]
             ]);
    }
}
