<?php

namespace Tests\GraphQL;

use Carbon\Carbon;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\CategoryFactory;
use Tests\CreatesApplication;
use Tests\TestCase;

class ValidateSquadTest extends TestCase
{

    use MakesGraphQLRequests;

    public static function setUpBeforeClass() : void
    {
        $knownDate = Carbon::create(2021, 12, 15, 12);
        Carbon::setTestNow($knownDate);
    }

    public static function tearDownAfterClass() : void
    {
        Carbon::setTestNow();
    }

    #[Test]
    public function useCase1()
    {
        // abc Aalborg - 2021-10-30
        $data = require __DIR__.'/SquadsUseCases/usecase1.php';

        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateSquads(input: $input) {
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $data
        )->assertExactJson([
            'data' => [
                'validateSquads' => ['playersWithoutPoints' => [], 'findings' => [
                    ["category"=>"HD","gender"=>"MEN","refId"=>"020202-1011"],
                    ["category"=>"HD","gender"=>"MEN","refId"=>"030610-05"],
                    ["category"=>"DD","gender"=>"WOMEN","refId"=>"040201-01"],
                    ["category"=>"DD","gender"=>"WOMEN","refId"=>"970512-21"]
                ]]
            ],
            'extensions' => [
                'lighthouse_subscriptions' => [
                    'channel' => null
                ]
            ]
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Test]
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
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => ['playersWithoutPoints' => [], 'findings' => [
                        [
                            'refId' => $women1->refId,
                            'category' => 'MD',
                            'gender' => 'WOMEN'
                        ],
                        [
                            'refId' => $men1->refId,
                            'category' => 'MD',
                            'gender' => 'MEN'
                        ]
                    ]]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @throws \JsonException
     */
    #[Test]
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
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => ['playersWithoutPoints' => [], 'findings' => [
                        [
                            'refId' => $men3->refId,
                            'category' => 'HS',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men4->refId,
                            'category' => 'HS',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men5->refId,
                            'category' => 'HS',
                            'gender' => 'MEN'
                        ],
                    ]]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @throws \JsonException
     */
    #[Test]
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
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => ['playersWithoutPoints' => [], 'findings' => [
                        [
                            'refId' => $men1->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men2->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men3->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men4->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                    ]]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @throws \JsonException
     */
    #[Test]
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
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $variables
        )
            ->assertExactJson([
                'data' => [
                    'validateSquads' => ['playersWithoutPoints' => [], 'findings' => [
                        [
                            'refId' => $women1->refId,
                            'category' => 'MD',
                            'gender' => 'WOMEN'
                        ],
                        [
                            'refId' => $men1->refId,
                            'category' => 'MD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men1->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men2->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men3->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                        [
                            'refId' => $men4->refId,
                            'category' => 'HD',
                            'gender' => 'MEN'
                        ],
                    ]]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }

    /**
     * @throws \JsonException
     */
    #[Test]
    public function validatingSquadWithoutMDAndDSAndDD(): void
    {
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
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $variables
        )
             ->assertExactJson([
                 'data' => [
                     'validateSquads' => ['playersWithoutPoints' => [], 'findings' => []]
                 ],
                 'extensions' => [
                     'lighthouse_subscriptions' => [
                         'channel' => null
                     ]
                 ]
             ]);
    }

    /**
     * @throws \JsonException
     */
    #[Test]
    public function validatingSquadWithoutMDAndDSAndDDAndErrorInHS(): void
    {
        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 250, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 200, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 99, 'HD', 150, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 150, 'HD', 100, 0, '101010-8');
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
                playersWithoutPoints { refId }
                findings {
                    refId
                    category
                    gender
                }
              }
            }
        ',
            $variables
        )
             ->assertExactJson([
                 'data' => [
                     'validateSquads' => ['playersWithoutPoints' => [], 'findings' => [
                         [
                             'refId' => $men3->refId,
                             'category' => 'HS',
                             'gender' => 'MEN'
                         ]
                     ]]
                 ],
                 'extensions' => [
                     'lighthouse_subscriptions' => [
                         'channel' => null
                     ]
                 ]
             ]);
    }

    private const SQUAD_ORDER_QUERY = /** @lang GraphQL */ '
        mutation ($input: [ValidateTeam!]!) {
          validateSquads(input: $input) {
            playersWithoutPoints { squadIndex refId name category }
            findings { refId category }
          }
        }
    ';

    /**
     * @param  Squad[]  $squads
     * @return array<string, mixed>
     */
    private function lineup(array $squads): array
    {
        $teams = array_map(static fn (Squad $squad, int $index) => ['name' => 'Team '.($index + 1), 'squad' => $squad], $squads, array_keys($squads));

        return ['input' => json_decode(SerializerHelper::getSerializer()->encode($teams, 'json'), true, 512, JSON_THROW_ON_ERROR)];
    }

    private function squad(array $categories): Squad
    {
        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->categories = $categories;

        return $squad;
    }

    #[Test]
    public function aSinglesPlayerWithoutPointsInTheirCategoryStopsTheComparison(): void
    {
        // With Sofie's DS points, Hanne's 200 under Mette's 50 would be a finding; nothing is compared without them.
        $mette = CategoryFactory::makeWomen('DS', 50, 'DD', 0, 0, '101010-1');
        $hanne = CategoryFactory::makeWomen('DS', 200, 'DD', 0, 0, '101010-2');
        $sofie = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-3');
        $sofie->name = 'Sofie Lund';
        $first = $this->squad([
            CategoryFactory::makeCategory('DS. 1', 'DS', CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-4')),
            CategoryFactory::makeCategory('DS. 2', 'DS', CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-5')),
        ]);
        $second = $this->squad([
            CategoryFactory::makeCategory('DS. 1', 'DS', $mette),
            CategoryFactory::makeCategory('DS. 2', 'DS', $hanne),
            CategoryFactory::makeCategory('DS. 3', 'DS', $sofie),
        ]);

        $this->graphQL(self::SQUAD_ORDER_QUERY, $this->lineup([$first, $second]))
            ->assertJsonPath('data.validateSquads', [
                'playersWithoutPoints' => [
                    ['squadIndex' => 1, 'refId' => '101010-3', 'name' => 'Sofie Lund', 'category' => 'DS'],
                ],
                'findings' => [],
            ]);
    }

    #[Test]
    public function aDoublesPlayerWithoutPointsInTheirCategoryStopsTheComparisonWithoutCrashing(): void
    {
        $men1 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-1');
        $men2 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-2');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 300, 0, '101010-3');
        $men4 = CategoryFactory::makeMen('HS', 0, 'MxH', 0, 0, '101010-4');
        $squad = $this->squad([
            CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2),
            CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4),
        ]);

        $this->graphQL(self::SQUAD_ORDER_QUERY, $this->lineup([$squad]))
            ->assertJsonPath('data.validateSquads', [
                'playersWithoutPoints' => [
                    ['squadIndex' => 0, 'refId' => '101010-4', 'name' => 'Player-HS-MxH', 'category' => 'HD'],
                ],
                'findings' => [],
            ]);
    }

    #[Test]
    public function missingPointsOnAPaperSquadOrInACategoryWithNothingToCompareDontStopTheComparison(): void
    {
        $paper = $this->squad([
            CategoryFactory::makeCategory('HS. 1', 'HS', CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-1')),
            CategoryFactory::makeCategory('HS. 2', 'HS', CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-2')),
        ]);
        $paper->isPaperSquad = true;
        // One DS on the Squad, so the DS player isn't compared with anyone.
        $squad = $this->squad([
            CategoryFactory::makeCategory('DS. 1', 'DS', CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-3')),
            CategoryFactory::makeCategory('HS. 1', 'HS', CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-4')),
            CategoryFactory::makeCategory('HS. 2', 'HS', CategoryFactory::makeMen('HS', 100, 'HD', 0, 0, '101010-5')),
        ]);

        $this->graphQL(self::SQUAD_ORDER_QUERY, $this->lineup([$squad, $paper]))
            ->assertJsonPath('data.validateSquads', [
                'playersWithoutPoints' => [],
                'findings' => [['refId' => '101010-4', 'category' => 'HS']],
            ]);
    }

    #[Test]
    public function completePointsGiveTheFindings(): void
    {
        $men1 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-1');
        $men2 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-2');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 300, 0, '101010-3');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-4');
        $squad = $this->squad([
            CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2),
            CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4),
        ]);

        $this->graphQL(self::SQUAD_ORDER_QUERY, $this->lineup([$squad]))
            ->assertJsonPath('data.validateSquads', [
                'playersWithoutPoints' => [],
                'findings' => [
                    ['refId' => '101010-1', 'category' => 'HD'],
                    ['refId' => '101010-2', 'category' => 'HD'],
                ],
            ]);
    }
}
