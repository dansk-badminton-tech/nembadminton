<?php

namespace Tests\GraphQL;

use FlyCompany\TeamFight\Models\SerializerHelper;
use FlyCompany\TeamFight\Models\Squad;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\CategoryFactory;
use Tests\CreatesApplication;

class ValidateCrossSquadsTest extends BaseTestCase
{

    use CreatesApplication;
    use MakesGraphQLRequests;

    /**
     * @test
     * @throws \JsonException
     */
    public function validTeamCross3Squads()
    {
        $squad1Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 800, '101010-1');
        $squad1Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 750, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 700, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 650, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1600, '101010-5');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1550, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1500, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1450, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1400, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1350, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad1Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad1Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad1Women1, $squad1Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $squad2Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 600, '101010-11');
        $squad2Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 550, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 500, '101010-13');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 450, '101010-14');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1300, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1250, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1200, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1150, '101010-18');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1100, '101010-19');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1050, '101010-20');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad2Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad2Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad2Women1, $squad2Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap2 = [
            'name' => 'Team 2',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 400, '101010-21');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 350, '101010-22');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 300, '101010-23');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 250, '101010-24');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1000, '101010-25');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 950, '101010-26');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 900, '101010-27');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 850, '101010-28');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 800, '101010-29');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 750, '101010-30');

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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap3 = [
            'name' => 'Team 3',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap1, $teamWrap2, $teamWrap3], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateCrossTeamInput!]!) {
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
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMixDoubleCross2Squads()
    {
        $squad1Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $squad1Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
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
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad1Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad1Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad1Women1, $squad1Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 51, '101010-11');
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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap2 = [
            'name' => 'Team 2',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap1, $teamWrap2], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateCrossTeamInput!]!) {
              validateCrossSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
            ->assertJson([
                'data' => [
                    'validateCrossSquads' => [
                        [
                            'refId' => $squad1Women1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad1Women1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad1Women2->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad1Women2->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMixDoubleCross3Squads()
    {
        $squad1Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $squad1Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
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
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad1Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad1Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad1Women1, $squad1Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $squad2Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-11');
        $squad2Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
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
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad2Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad2Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad2Women1, $squad2Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap2 = [
            'name' => 'Team 2',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 51, '101010-21');
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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap3 = [
            'name' => 'Team 3',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap1, $teamWrap2, $teamWrap3], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateCrossTeamInput!]!) {
              validateCrossSquads(input: $input) {
                refId
                category
                gender
              }
            }
        ',
            $variables
        )
            ->assertJson([
                'data' => [
                    'validateCrossSquads' => [
                        [
                            'refId' => $squad1Women1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad1Women1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad1Women2->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad1Women2->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad2Women1->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad2Women1->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad2Women2->refId,
                            'category' => 'MD',
                            'gender' => 'K'
                        ],
                        [
                            'refId' => $squad2Women2->refId,
                            'category' => 'DD',
                            'gender' => 'K'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function playingToHighInMixDoubleCross3SquadsCheckBelowPlayers()
    {
        $squad1Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-1');
        $squad1Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-2');
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
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad1Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad1Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad1Women1, $squad1Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $squad2Women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-11');
        $squad2Women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 0, '101010-12');
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
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $squad2Women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $squad2Women2, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 1', 'DS', $women3);
        $squad->categories[] = CategoryFactory::makeCategory('DS. 2', 'DS', $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 1', 'HS', $men3);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 2', 'HS', $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 3', 'HS', $men5);
        $squad->categories[] = CategoryFactory::makeCategory('HS. 4', 'HS', $men6);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 1', 'DD', $squad2Women1, $squad2Women2);
        $squad->categories[] = CategoryFactory::makeCategory('DD. 2', 'DD', $women3, $women4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap2 = [
            'name' => 'Team 2',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 51, '101010-21');
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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap3 = [
            'name' => 'Team 3',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap1, $teamWrap2, $teamWrap3], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateCrossTeamInput!]!) {
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
            $variables
        )
            ->assertJson([
                'data' => [
                    'validateCrossSquads' => [
                        [
                            'refId' => $squad1Women1->refId,
                            'category' => 'MD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad1Women1->refId,
                            'category' => 'DD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad1Women2->refId,
                            'category' => 'MD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad1Women2->refId,
                            'category' => 'DD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad2Women1->refId,
                            'category' => 'MD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad2Women1->refId,
                            'category' => 'DD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad2Women2->refId,
                            'category' => 'MD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ],
                        [
                            'refId' => $squad2Women2->refId,
                            'category' => 'DD',
                            'gender' => 'K',
                            'belowPlayer' => [
                                [
                                    'name' => $women1->name,
                                    'refId' => $women1->refId
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @test
     * @throws \JsonException
     */
    public function SpecialCase()
    {
        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 1000, '101010-1');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 1000, '101010-2');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 1000, '101010-3');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 1000, '101010-4');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1000, '101010-5');
        $specialMen2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 500, '101010-6');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-7');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-8');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-9');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-10');

        $squad = new Squad();
        $squad->playerLimit = 10;
        $squad->league = 'OTHER';
        $squad->categories[] = CategoryFactory::makeCategory('MD. 1', 'MD', $women1, $men1);
        $squad->categories[] = CategoryFactory::makeCategory('MD. 2', 'MD', $women2, $specialMen2);
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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $specialMen2);

        $teamWrap1 = [
            'name' => 'Team 1',
            'squad' => $squad
        ];

        $women1 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 1000, '101010-11');
        $women2 = CategoryFactory::makeWomen('MxD', 0, 'DD', 0, 1000, '101010-12');
        $women3 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 1000, '101010-13');
        $women4 = CategoryFactory::makeWomen('DS', 0, 'DD', 0, 1000, '101010-14');

        $men1 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1000, '101010-15');
        $men2 = CategoryFactory::makeMen('MxH', 0, 'HD', 0, 1000, '101010-16');
        $men3 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-17');
        $men4 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-18');
        $men5 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-19');
        $men6 = CategoryFactory::makeMen('HS', 0, 'HD', 0, 1000, '101010-20');

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
        $squad->categories[] = CategoryFactory::makeCategory('HD. 1', 'HD', $men1, $men2);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 2', 'HD', $men3, $men4);
        $squad->categories[] = CategoryFactory::makeCategory('HD. 3', 'HD', $men5, $men6);

        $teamWrap2 = [
            'name' => 'Team 2',
            'squad' => $squad
        ];

        $teams = SerializerHelper::getSerializer()->encode([$teamWrap1, $teamWrap2], 'json');

        $variables = ['input' => json_decode($teams, true, 512, JSON_THROW_ON_ERROR)];
        $this->graphQL(
        /** @lang GraphQL */ '
            mutation ($input: [ValidateCrossTeamInput!]!) {
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
            $variables
        )
            ->assertJson([
                'data' => [
                    'validateCrossSquads' => [
                        [
                            'refId' => $specialMen2->refId,
                            'category' => 'MD',
                            'gender' => 'M',
                            'belowPlayer' => [
                                [
                                    'name' => $men1->name,
                                    'refId' => $men1->refId,
                                ],
                                [
                                    'name' => $men2->name,
                                    'refId' => $men2->refId,
                                ],
                            ]
                        ],
                        [
                            'refId' => $specialMen2->refId,
                            'category' => 'HD',
                            'gender' => 'M',
                            'belowPlayer' => [
                                [
                                    'name' => $men1->name,
                                    'refId' => $men1->refId,
                                ],
                                [
                                    'name' => $men2->name,
                                    'refId' => $men2->refId,
                                ],
                            ]
                        ],

                    ]
                ]
            ]);
    }
}
