<?php


namespace GraphQL;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\CreatesApplication;

class CancellationCollectorTest extends TestCase
{

    use CreatesApplication;
    use MakesGraphQLRequests;

    /**
     * @test
     * @skip
     * */
    public function it_can_create_a_cancellation_collector()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Build your input here
        $input = [
            'email' => 'test@gmail.com',
        ];

        $this->graphQL(
        /** @lang GraphQL */ '
                mutation ($input: CancellationCollectionInput!){
                  createCancellationCollector(input: $input) {
                    id
                  }
                }
            ',
            [
                'input' => $input,
            ])
             ->assertExactJson([
                 'data'       => [
                     'createCancellationCollector' => [
                         'email' => 'test@gmail.com',
                     ],
                 ],
                 'extensions' => [
                     'lighthouse_subscriptions' => [
                         'channel' => null,
                     ],
                 ],
             ]);
    }

}
