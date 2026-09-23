<?php

namespace Tests\GraphQL;

use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class MembersSearchTest extends TestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    protected string $seeder = 'RolesAndPermissionsSeeder';

    /** @test */
    public function it_ignores_a_null_not_on_scenario_filter(): void
    {
        $clubhouse = Clubhouse::factory()->create();
        $user = User::factory()->create([
            'clubhouse_id' => $clubhouse->id,
            'primary_role_id' => null,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            query($clubhouse: Int!, $notOnScenario: ID) {
                membersSearch(
                    clubhouse: $clubhouse
                    notOnScenario: $notOnScenario
                ) {
                    data { id }
                }
            }
        ', [
            'clubhouse' => $clubhouse->id,
            'notOnScenario' => null,
        ]);

        $response->assertJsonMissingPath('errors')
            ->assertJsonPath('data.membersSearch.data', []);
    }
}
