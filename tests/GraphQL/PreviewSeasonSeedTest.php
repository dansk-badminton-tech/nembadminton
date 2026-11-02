<?php

declare(strict_types=1);

namespace Tests\GraphQL;

use App\Models\TeamRound;
use App\Models\User;
use Database\Seeders\PreviewClubDataSeeder;
use Database\Seeders\PreviewDataSeeder;
use Database\Seeders\PreviewSeasonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\TestCase;

class PreviewSeasonSeedTest extends TestCase
{
    use MakesGraphQLRequests;
    use ValidatesSeededLineups;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh', ['--seed' => true, '--seeder' => PreviewDataSeeder::class]);
        $this->actingAs(User::query()->findOrFail(1), 'api');
        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:fresh');
            RefreshDatabaseState::$migrated = false;
        });
    }

    public function test_preview_has_only_one_linked_season_and_named_rounds(): void
    {
        $this->assertSame([2025], DB::table('seasons')->pluck('id')->all());
        $this->assertSame(['2025/2026'], DB::table('seasons')->pluck('season_name')->all());
        $rounds = TeamRound::query()->orderBy('round')->get();
        $this->assertSame(range(1, 11), $rounds->pluck('round')->all());
        $this->assertSame([2025], $rounds->pluck('season_id')->unique()->values()->all());
        $this->assertSame(4, DB::table('teams')->where('season_id', 2025)->count());
        $this->assertSame(0, DB::table('squads')->whereNull('team_id')->count());
        $this->assertSame(0, DB::table('teams')->where('season_id', '!=', 2025)->count());

        foreach (PreviewClubDataSeeder::fixture()['rounds'] as $source) {
            $round = $rounds->firstWhere('round', $source['round']);
            $this->assertSame('Runde '.$round->round.' · '.PreviewSeasonSeeder::HINTS[$round->round], $round->name);
            $this->assertSame($source['game_date'], $round->game_date);
            $this->assertSame($source['version'], $round->version);
            foreach ($round->squads as $squad) {
                $this->assertSame(13, $squad->categories()->whereNull('team_round_scenario_id')->count());
            }
        }
    }

    public function test_official_lineups_pass_normal_checks(): void
    {
        foreach (TeamRound::query()->orderBy('round')->get() as $round) {
            $input = $this->validationInput($round, null);
            $label = 'Round '.$round->round;
            $this->assertNotContains(false, $this->spotsFulfilled($input), $label);
            $this->assertSame(['playersWithoutPoints' => [], 'findings' => []], $this->mutate('validateSquads', $input, 'playersWithoutPoints { name } findings { category }'), $label);
            $this->assertSame([], $this->teamRoundCrossSquads($round, null, 'category'), $label);
            $this->assertSame([], $this->mutate('validateReserves', $input, 'category'), $label);
            $this->assertSame([], $this->graphQL(
                'mutation ($input: [ValidateTeam!]!) { validateTooYoungPlayers(input: $input, seasonStartYear: 2025) { name } }',
                ['input' => $input]
            )->assertJsonMissingPath('errors')->json('data.validateTooYoungPlayers'), $label);
            foreach (['validateFinalRoundEligibility', 'validatePlayoffParticipation', 'validateQualificationEligibility'] as $check) {
                $statuses = $this->graphQL(
                    'mutation ($id: ID!) { '.$check.'(teamRoundId: $id) { players { status } } }',
                    ['id' => $round->id]
                )->assertJsonMissingPath('errors')->json('data.'.$check.'.players');
                $this->assertNotContains('REQUIREMENT_NOT_MET', array_column($statuses, 'status'), $label.' '.$check);
                $this->assertNotContains('CANNOT_VERIFY', array_column($statuses, 'status'), $label.' '.$check);
            }

            $placements = [];
            foreach ($input as $team) {
                $squad = $team['squad'];
                $appearances = collect($squad['categories'])->flatMap(fn ($category) => array_column($category['players'], 'refId'))->countBy();
                $this->assertLessThanOrEqual($squad['playerLimit'], $appearances->count(), $label);
                foreach ($appearances as $ref => $count) {
                    $this->assertLessThanOrEqual(2, $count, $label);
                    $placements[$ref][] = $squad['id'];
                }
            }
            $reserves = DB::table('squad_reserves')->whereIn('squad_id', $round->squads->modelKeys())->pluck('reserve_ref_id')->all();
            foreach ($placements as $ref => $squadIds) {
                if (count($squadIds) > 1) {
                    $this->assertContains((string) $ref, $reserves, $label.' has a duplicate Member without a Reserve Player mark');
                }
            }
        }
    }

    public function test_paper_squads_have_the_findings_named_in_the_round(): void
    {
        $findings = [];
        foreach ([4, 6, 10] as $number) {
            $round = TeamRound::query()->findOrFail(PreviewSeasonSeeder::roundId($number));
            $this->assertSame(1, $round->squads->where('is_paper_squad', true)->count());
            $result = $this->graphQL(
                'mutation ($id: ID!, $input: [ValidateTeam!]!) { validatePaperSquads(teamRoundId: $id, input: $input) { missingRound players { matchCount requiredMatchCount unplayedCategories uncountedRounds { round } } } }',
                ['id' => $round->id, 'input' => $this->validationInput($round, null)]
            )->assertJsonMissingPath('errors')->json('data.validatePaperSquads');
            $this->assertFalse($result['missingRound']);
            $findings[$number] = $result['players'];
        }
        $this->assertSame([
            4 => [],
            6 => [
                ['matchCount' => 5, 'requiredMatchCount' => 1, 'unplayedCategories' => ['MD'], 'uncountedRounds' => []],
                ['matchCount' => 0, 'requiredMatchCount' => 1, 'unplayedCategories' => ['DS', 'DD'], 'uncountedRounds' => []],
            ],
            10 => [
                ['matchCount' => 1, 'requiredMatchCount' => 2, 'unplayedCategories' => [], 'uncountedRounds' => []],
            ],
        ], $findings);
    }

    public function test_reserve_quarantine_and_cancellations_are_respected(): void
    {
        $this->assertSame(1, DB::table('squad_reserves')->count());
        $reserve = DB::table('squad_reserves')->sole();
        $this->assertSame(PreviewSeasonSeeder::roundId(3), DB::table('squads')->where('id', $reserve->squad_id)->value('team_round_id'));
        $roundFour = $this->validationInput(TeamRound::query()->findOrFail(PreviewSeasonSeeder::roundId(4)), null);
        $roundFourRefs = collect($roundFour)->flatMap(fn ($team) => collect($team['squad']['categories'])->flatMap(fn ($category) => array_column($category['players'], 'refId')))->all();
        $this->assertNotContains($reserve->reserve_ref_id, $roundFourRefs);

        foreach (DB::table('cancellations')->join('cancellation_dates', 'cancellations.id', '=', 'cancellation_dates.cancellation_id')->get() as $absence) {
            $round = TeamRound::query()->whereDate('game_date', $absence->date)->sole();
            $refs = collect($this->validationInput($round, null))->flatMap(fn ($team) => collect($team['squad']['categories'])->flatMap(fn ($category) => array_column($category['players'], 'refId')))->all();
            $this->assertNotContains($absence->refId, $refs, 'Round '.$round->round);
        }
    }

    public function test_ordering_error_is_only_in_the_draft(): void
    {
        $round = TeamRound::query()->findOrFail(PreviewSeasonSeeder::roundId(11));
        $scenario = DB::table('team_round_scenarios')->where('team_round_id', $round->id)->sole();
        $this->assertSame(0, $scenario->is_official);
        $this->assertSame(['playersWithoutPoints' => [], 'findings' => []], $this->mutate('validateSquads', $this->validationInput($round, null), 'playersWithoutPoints { name } findings { category }'));
        $result = $this->mutate('validateSquads', $this->validationInput($round, $scenario->id), 'playersWithoutPoints { name } findings { category }');
        $this->assertSame([], $result['playersWithoutPoints']);
        $findings = $result['findings'];
        $this->assertCount(2, $findings);
        $this->assertSame(['HD'], array_values(array_unique(array_column($findings, 'category'))));
    }

    public function test_fixture_and_seeded_accounts_contain_only_allowlisted_data(): void
    {
        $data = PreviewClubDataSeeder::fixture();
        $this->assertSame(['club', 'members', 'points', 'rounds'], array_keys($data));
        foreach ($data['members'] as $member) {
            $this->assertEqualsCanonicalizing(['refId', 'name', 'gender'], array_keys($member));
        }
        foreach ($data['points'] as $point) {
            $this->assertEqualsCanonicalizing(['refId', 'points', 'position', 'category', 'vintage', 'version'], array_keys($point));
        }
        $this->assertSame(['testing@gmail.com'], DB::table('users')->pluck('email')->all());
        $this->assertSame(['Previewbruger'], DB::table('users')->pluck('name')->all());
        $this->assertSame(['BC37 Amager · Eksempel'], DB::table('clubhouses')->pluck('name')->all());
        $this->assertSame(0, DB::table('members')->whereNotNull('birthday')->count());
        $this->assertSame(1, DB::table('clubhouses')->count());
        $this->assertSame(1, DB::table('clubs')->count());
        $this->assertFalse(DB::table('team_rounds')->where('name', 'like', 'Ungdom%')->exists());
        $this->assertFalse(DB::table('teams')->where('name', 'like', '%U17%')->exists());
    }
}
