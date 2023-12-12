<?php

namespace Tests\GraphQL;

use Carbon\Carbon;
use Closure;
use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\CategoryFactory;
use Tests\CreatesApplication;

class ValidateCrossSquadsLeagueTest extends BaseTestCase
{

    use CreatesApplication;
    use MakesGraphQLRequests;

    private function fixedInTime(Closure $callback) : void
    {
        $knownDate = Carbon::create(2021, 12, 15, 12);
        Carbon::withTestNow($knownDate, $callback);
    }

    /**
     * @test
     */
    public function useCase5(): void
    {
        $this->fixedInTime(function(){
            // https://github.com/flycompanytech/holdkamp-project/issues/31
            $data = require __DIR__.'/CrossSquadsUseCases/usecase5.php';

            $this->graphQL(
            /** @lang GraphQL */ '
                mutation ($input: [ValidateTeam!]!) {
                  validateCrossSquads(input: $input) {
                    refId
                    name
                    category
                  }
                }
            ',
                $data
            )->assertExactJson([
                'data' => [
                    'validateCrossSquads' => []
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
        });
    }

    /**
     * @test
     */
    public function useCase4(): void
    {
        $this->fixedInTime(function(){
            // https://github.com/flycompanytech/holdkamp-project/issues/30
            $data = require __DIR__.'/CrossSquadsUseCases/usecase4.php';
            $this->graphQL(
            /** @lang GraphQL */ '
                mutation ($input: [ValidateTeam!]!) {
                  validateCrossSquads(input: $input) {
                    refId
                    name
                    category
                  }
                }
            ',
                $data
            )->assertJsonMissing(
                [
                    [
                        'category' => 'DD',
                        'name' => 'Signe Schulz Terp-Nielsen',
                        'refId' => '010626-02'
                    ]
                ]
            );
        });
    }

    /**
     * @test
     */
    public function useCase3(): void
    {
        $this->fixedInTime(function() {
            // abc Aalborg - 2021-10-30
            $data = require __DIR__ . '/CrossSquadsUseCases/usecase3.php';

            $this->graphQL(
            /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
                refId
                name
                category
                gender
                isYouthPlayer
                belowPlayer{
                    name
                    refId
                    isYouthPlayer
                }
              }
            }
        ',
                $data
            )->assertExactJson([
                'data'       => [
                    'validateCrossSquads' => []
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
        });
    }

    /**
     * @test
     */
    public function useCase2(): void
    {
        $this->fixedInTime(function() {
            // abc Aalborg - 2021-10-30
            $data = require __DIR__ . '/CrossSquadsUseCases/usecase2.php';

            $this->graphQL(
            /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
                refId
                category
                gender
                belowPlayer{
                    name
                    refId
                }
              }
            }
        ',
                $data
            )->assertExactJson([
                'data'       => [
                    'validateCrossSquads' => []
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
        });
    }

    /**
     * @test
     */
    public function useCase1()
    {
        $this->fixedInTime(function() {
            $data = require __DIR__ . '/CrossSquadsUseCases/usecase1.php';

            $this->graphQL(
            /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
                refId
                category
                gender
                belowPlayer{
                    name
                    refId
                }
              }
            }
        ',
                $data
            )->assertExactJson([
                'data'       => [
                    'validateCrossSquads' => [
                        [
                            'refId'       => '951108-04',
                            'category'    => 'MD',
                            'gender'      => 'K',
                            'belowPlayer' => [
                                [
                                    "name"  => "Maibritt Meldgaard",
                                    "refId" => "981105-05"
                                ],
                                [
                                    "name"  => "Caroline Bohm Veng",
                                    "refId" => "000128-10"
                                ],
                                [
                                    "name"  => "Louise Bolding Lund",
                                    "refId" => "990618-10"
                                ],
                                [
                                    "name"  => "Anna Schøn",
                                    "refId" => "900216-01"
                                ]
                            ]
                        ]
                    ]
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
        });
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function shouldOnlyCompareLeagueWithFirstDivisionAndFirstDivisionWithDenmarkSeries()
    {
        $womenSpecial1 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-1');
        $womenSpecial2 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 100, 0, '101010-3');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'LIGA';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $womenSpecial1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $womenSpecial2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $womenSpecial1, $womenSpecial2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $leagueTeam = [
            'name' => 'Liga',
            'squad' => $squad
        ];

        $womenSquad2Special1 = CategoryFactory::makeWomen('MxD', 101, 'DD', 101, 0, '101010-11');
        $womenSquad2Special2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'FIRSTDIVISION';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $womenSquad2Special1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $womenSquad2Special2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $womenSquad2Special1, $womenSquad2Special2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $firstDivTeam = [
            'name' => '1. Div',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 152, 'DD', 152, 0, '101010-19');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-20');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-21');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-22');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-23');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-24');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-25');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-26');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-27');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-28');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'OTHER';
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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men6);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men2);

        $denmarkSerieTeam = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$leagueTeam, $firstDivTeam, $denmarkSerieTeam], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR), 'rules' => 'LEAGUE1DIV'];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
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
                    'validateCrossSquads' => [
                        [
                            'refId' => $womenSpecial1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial2->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial2->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSquad2Special1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSquad2Special1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSquad2Special2->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSquad2Special2->refId,
                            'category' => 'DD',
                            'gender' => 'K'
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
    public function twoWomenOver50PointsInMDAndDD()
    {
        $womenSpecial1 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-1');
        $womenSpecial2 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 100, 0, '101010-3');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'LIGA';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $womenSpecial1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $womenSpecial2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $womenSpecial1, $womenSpecial2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $leagueTeam = [
            'name' => 'Liga',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 101, 'DD', 101, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'FIRSTDIVISION';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $firstDivTeam = [
            'name' => '1. Div',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$leagueTeam, $firstDivTeam], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR), 'rules' => 'LEAGUE1DIV'];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
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
                    'validateCrossSquads' => [
                        [
                            'refId' => $womenSpecial1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial2->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial2->refId,
                            'category' => 'DD',
                            'gender' => 'K'
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
    public function womenWithin50PointsInMDButOverInDDShouldNotShow()
    {
        $womenSpecial1 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 50, 'DD', 100, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 100, 0, '101010-3');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-8');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'LIGA';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $womenSpecial1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $womenSpecial1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $leagueTeam = [
            'name' => 'Liga',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 75, 'DD', 101, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'FIRSTDIVISION';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $firstDivTeam = [
            'name' => '1. Div',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$leagueTeam, $firstDivTeam], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR), 'rules' => 'LEAGUE1DIV'];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
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
                    'validateCrossSquads' => []
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
    public function oneWomenOverInMDAndDD()
    {
        $womenSpecial1 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 102, 'DD', 102, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 102, 'DD', 102, 0, '101010-3');

        $men1 = CategoryFactory::makeMen('MxH', 50, 'HD', 50, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 50, 'HD', 50, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-8');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'LIGA';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $womenSpecial1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $womenSpecial1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $leagueTeam = [
            'name' => 'Liga',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 101, 'DD', 101, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'FIRSTDIVISION';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $firstDivTeam = [
            'name' => '1. Div',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$leagueTeam, $firstDivTeam], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR), 'rules' => 'LEAGUE1DIV'];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
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
                    'validateCrossSquads' => [
                        [
                            'refId' => $womenSpecial1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $womenSpecial1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ]
                    ],
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
    public function womenWithin50PointsInMDShouldNotShow(): void
    {
        $womenSpecial1 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 50, 0, '101010-3');

        $men1 = CategoryFactory::makeMen('MxH', 50, 'HD', 50, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 50, 'HD', 50, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-8');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'LIGA';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $womenSpecial1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $womenSpecial1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $leagueTeam = [
            'name' => 'Liga',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 75, 'DD', 0, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'FIRSTDIVISION';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $firstDivTeam = [
            'name' => '1. Div',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$leagueTeam, $firstDivTeam], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR), 'rules' => 'LEAGUE1DIV'];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
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
                    'validateCrossSquads' => []
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
    public function validHappyScenario()
    {
        $women1 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 50, 'DD', 50, 0, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 50, 'DD', 50, 0, '101010-3');

        $men1 = CategoryFactory::makeMen('MxH', 50, 'HD', 50, 0, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 50, 'HD', 50, 0, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 50, 'HD', 50, 0, '101010-8');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'LIGA';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $leagueTeam = [
            'name' => 'Liga',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 0, '101010-13');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 0, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 0, '101010-18');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'FIRSTDIVISION';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $women1, $women2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);

        $firstDivTeam = [
            'name' => '1. Div',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$leagueTeam, $firstDivTeam], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR), 'rules' => 'LEAGUE1DIV'];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateTeam!]!) {
              validateCrossSquads(input: $input) {
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
                    'validateCrossSquads' => []
                ],
                'extensions' => [
                    'lighthouse_subscriptions' => [
                        'channel' => null
                    ]
                ]
            ]);
    }
}
