<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Database\Seeders\PreviewDataSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

/**
 * The seeded 2026/2027 Season of BC37 Amager (see PaperSquadSeasonSeeder) shows the paper-team rule (§38 stk. 7): four
 * Teams with the official match counts, valid Official Lineups, and three Paper Squads with known findings.
 */
class PaperSquadSeasonSeedTest extends TestCase
{
    use MakesGraphQLRequests;
    use ValidatesSeededLineups;

    protected function setUp(): void
    {
        parent::setUp();

        // PreviewDataSeeder needs fresh auto-increment ids (Clubhouse 1, User 1), which
        // RefreshDatabase and DatabaseTruncation don't give after other tests have run.
        $this->artisan('migrate:fresh', ['--seed' => true, '--seeder' => PreviewDataSeeder::class]);
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:fresh');
            // The next RefreshDatabase test must migrate and seed again: this test emptied its seeded tables.
            RefreshDatabaseState::$migrated = false;
        });
    }

    public function test_the_club_has_four_teams_with_the_official_match_counts(): void
    {
        $teams = Team::query()->where('clubhouse_id', 1)->where('season_id', 2026)->orderBy('id')->get();
        $this->assertSame(
            ['Badmintonligaen', '1. division', '2. division', '3. division'],
            $teams->map(fn (Team $team) => $team->tierName())->all()
        );

        foreach ($this->seasonRounds() as $round) {
            $this->assertSame(
                [9, 9, 13, 13],
                $round->squads->map(fn (Squad $squad) => $squad->categories()->count())->all(),
                'Round '.$round->round
            );
        }
    }

    public function test_every_official_lineup_follows_the_rules(): void
    {
        $rounds = $this->seasonRounds();

        $this->assertSame(range(1, 11), $rounds->pluck('round')->all());

        foreach ($rounds as $round) {
            $input = $this->validationInput($round, null);
            $label = 'Round '.$round->round;

            $this->assertNotContains(false, $this->spotsFulfilled($input), $label);
            $this->assertSame([], $this->mutate('validateSquads', $input, 'name category belowPlayer { name }'), $label);
            $this->assertSame([], $this->mutate('validateCrossSquads', $input, 'name belowPlayer { name category }'), $label);
            $this->assertSame([], $this->teamRoundCrossSquads($round, null, 'name belowPlayer { name category }'), $label);
        }
    }

    public function test_the_paper_squads_show_each_finding_of_the_paper_team_rule(): void
    {
        $this->actingAs(User::query()->findOrFail(1), 'api');

        $findings = $this->seasonRounds()
            ->filter(fn (TeamRound $round) => $round->squads->contains('is_paper_squad', true))
            ->mapWithKeys(fn (TeamRound $round) => [$round->round => [
                'paperSquadTier' => $round->squads->firstWhere('is_paper_squad', true)->team->tierName(),
                'players' => $this->graphQL(
                    'mutation ($id: ID!, $input: [ValidateTeam!]!) { validatePaperSquads(teamRoundId: $id, input: $input) { missingRound players { name unplayedCategories matchCount requiredMatchCount uncountedRounds { name round } } } }',
                    ['id' => $round->id, 'input' => $this->validationInput($round, null)]
                )->assertJsonMissingPath('errors')->assertJsonPath('data.validatePaperSquads.missingRound', false)->json('data.validatePaperSquads.players'),
            ]])
            ->all();

        $this->assertSame([
            // The opponent withdrew; every player meets the rule.
            4 => ['paperSquadTier' => '2. division', 'players' => []],
            6 => ['paperSquadTier' => '1. division', 'players' => [
                // Back from injury: no earlier match, and no earlier DS or MD.
                ['name' => 'Signe Markwall', 'unplayedCategories' => ['DS', 'MD'], 'matchCount' => 0, 'requiredMatchCount' => 1, 'uncountedRounds' => []],
                ['name' => 'Katrine Tingleff', 'unplayedCategories' => ['DD'], 'matchCount' => 4, 'requiredMatchCount' => 1, 'uncountedRounds' => []],
            ]],
            // A bye in the quarter-final. Back from injury in round 9, whose lineups have no Team linked: that match
            // can't be counted, and the play-offs need two.
            10 => ['paperSquadTier' => 'Badmintonligaen', 'players' => [
                ['name' => 'Josefine Eggert Jackson', 'unplayedCategories' => [], 'matchCount' => 0, 'requiredMatchCount' => 2, 'uncountedRounds' => [['name' => 'Runde 9', 'round' => 9]]],
            ]],
        ], $findings);
    }

    /**
     * The seeded Season's TeamRounds, in round order.
     *
     * @return Collection<int, TeamRound>
     */
    private function seasonRounds(): Collection
    {
        return TeamRound::query()
            ->where('clubhouse_id', 1)
            ->where('season_id', 2026)
            ->with(['squads' => fn ($query) => $query->orderBy('order'), 'squads.team.tier'])
            ->orderBy('round')
            ->get();
    }
}
