<?php

namespace Tests\GraphQL;

use App\Models\Clubhouse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class UpdateMeTest extends TestCase
{
    use RefreshDatabase;
    use MakesGraphQLRequests;

    /** @test */
    public function it_allows_the_user_to_update_their_profile_including_unclaimed_player_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@email.com',
            'player_id' => null,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateMe!) {
                updateMe(input: $input) {
                    id
                    name
                    email
                    player_id
                }
            }
        ', [
            'input' => [
                'name' => 'Updated Name',
                'email' => 'updated@email.com',
                'player_id' => '010203-01',
            ]
        ]);



        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@email.com',
            'player_id' => '010203-01',
        ]);
    }

    /** @test */
    public function it_allows_updating_profile_while_keeping_own_player_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@email.com',
            'player_id' => '010203-01',
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateMe!) {
                updateMe(input: $input) {
                    id
                    name
                    email
                    player_id
                }
            }
        ', [
            'input' => [
                'name' => 'Updated Name',
                'email' => 'original@email.com',
                'player_id' => '010203-01',
            ]
        ]);

        $response->assertJson([
            'data' => [
                'updateMe' => [
                    'name' => 'Updated Name',
                    'email' => 'original@email.com',
                    'player_id' => '010203-01',
                ]
            ]
        ]);
    }

    /** @test */
    public function it_fails_validation_if_player_id_is_already_claimed_in_same_clubhouse(): void
    {
        $clubhouse = Clubhouse::factory()->create();

        $otherUser = User::factory()->create([
            'email' => 'other@email.com',
            'player_id' => '010203-01',
            'clubhouse_id' => $clubhouse->id,
        ]);

        $user = User::factory()->create([
            'name' => 'My Name',
            'email' => 'my@email.com',
            'player_id' => null,
            'clubhouse_id' => $clubhouse->id,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateMe!) {
                updateMe(input: $input) {
                    id
                    name
                    email
                    player_id
                }
            }
        ', [
            'input' => [
                'name' => 'My Name',
                'email' => 'my@email.com',
                'player_id' => '010203-01',
            ]
        ]);

        $response->assertGraphQLValidationKeys(['input.player_id']);
        $this->assertEquals(
            'Denne spiller er allerede tilknyttet en anden bruger.',
            $response->json('errors.0.extensions.validation')['input.player_id'][0]
        );
    }

    /** @test */
    public function it_allows_same_player_id_in_different_clubhouses(): void
    {
        $clubhouseA = Clubhouse::factory()->create();
        $clubhouseB = Clubhouse::factory()->create();

        $otherUser = User::factory()->create([
            'email' => 'other@email.com',
            'player_id' => '010203-01',
            'clubhouse_id' => $clubhouseA->id,
        ]);

        $user = User::factory()->create([
            'name' => 'My Name',
            'email' => 'my@email.com',
            'player_id' => null,
            'clubhouse_id' => $clubhouseB->id,
        ]);

        $this->actingAs($user, 'api');

        $response = $this->graphQL(/** @lang GraphQL */ '
            mutation($input: UpdateMe!) {
                updateMe(input: $input) {
                    id
                    name
                    email
                    player_id
                }
            }
        ', [
            'input' => [
                'name' => 'My Name',
                'email' => 'my@email.com',
                'player_id' => '010203-01',
            ]
        ]);

        $response->assertJson([
            'data' => [
                'updateMe' => [
                    'id' => (string) $user->id,
                    'name' => 'My Name',
                    'email' => 'my@email.com',
                    'player_id' => '010203-01',
                ]
            ]
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'player_id' => '010203-01',
            'clubhouse_id' => $clubhouseB->id,
        ]);
    }
}
