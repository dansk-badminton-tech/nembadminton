<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\Member;
use App\Models\Squad;
use App\Models\Team;
use App\Models\TeamRound;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamRoundFinalRoundEligibilityTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private const NOT_COVERED = 'Gælder kun runde 11–12';

    public function test_the_check_starts_on_can_be_run_again_and_its_switch_is_remembered(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        [$round] = $this->finalRoundWithMembers(['name' => 'Client validation control'], [$member]);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckDetails('finalRound', $member->name)
                ->assertSeeIn('@check-status-finalRound', 'Fejl')
                ->assertSeeIn('@check-section-action', 'Tidligere hold');
            $browser->script(<<<'JS'
                const originalFetch = window.fetch;
                window.finalRoundRequests = [];
                window.fetch = function (url, options) {
                    const request = options?.body ? JSON.parse(options.body) : null;
                    if (request?.operationName === 'validateFinalRoundEligibility') {
                        window.finalRoundRequests.push(request.variables);
                    }
                    return originalFetch(url, options);
                };
            JS);
            $browser->click('@check-revalidate-finalRound')
                ->waitUntil('window.finalRoundRequests.length > 0')
                ->assertScript('Object.keys(window.finalRoundRequests[0]).sort().join(",")', 'scenarioId,teamRoundId')
                ->assertScript('window.finalRoundRequests[0].teamRoundId', $round->id)
                ->assertScript('window.finalRoundRequests[0].scenarioId', null)
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->assertSeeIn('@check-details-finalRound', 'Slået fra. Slå til herunder.')
                ->assertDontSeeIn('@check-details-finalRound', $member->name)
                ->assertMissing('@check-revalidate-finalRound')
                // The choice belongs to the Team Round, not to its round number.
                ->changeRoundNumber(12)
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->switchCheck('finalRound')
                ->waitForCheckDetails('finalRound', $member->name)
                ->assertPresent('@check-revalidate-finalRound')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->refresh()->on(new TeamFightEditPage)
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->openCheck('finalRound')
                ->assertDontSeeIn('@check-details-finalRound', $member->name);
        });
    }

    public function test_deleting_the_selected_draft_returns_to_the_official_scenario(): void
    {
        $round = TeamRound::query()->create([
            'name' => 'Scenario deletion', 'game_date' => '2026-10-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 11, 'clubhouse_id' => Clubhouse::firstOrFail()->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $round->scenarios()->create(['name' => 'Officiel', 'is_official' => true]);
        $round->scenarios()->create(['name' => 'Udkast', 'is_official' => false]);
        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitFor('@scenario-selector-dropdown')
                ->selectScenario('Udkast')->assertScenarioSelected('Udkast', false);
            // Without Squads the server has nothing to call Gælder ikke, so the switched-off Check stays Slået fra.
            $browser->confirmScenarioDeletion()->waitUntilMissing('@scenario-selector-dropdown')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->assertMissing('@scenario-draft-warning-banner');
        });
    }

    public function test_pending_and_failed_checks_can_be_retried(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        [$round] = $this->finalRoundWithMembers(['name' => 'Validation retry'], [$member]);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckDetails('finalRound', $member->name)
                ->createScenarioFromOfficial('Tomt udkast')
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitForCheckStatus('finalRound', 'OK')
                ->selectScenario('Officiel opstilling')
                ->waitForCheckDetails('finalRound', $member->name);

            // Delay real HTTP responses at the network boundary, not Vue/Apollo internals.
            $browser->script(<<<'JS'
                const originalFetch = window.fetch;
                window.eligibilityNetwork = {hold: new Set(['validateFinalRoundEligibility']), release: {}, failNext: false};
                window.fetch = async function (url, options) {
                    const operation = options?.body ? JSON.parse(options.body).operationName : null;
                    const network = window.eligibilityNetwork;
                    if (operation === 'teamRound' && network.failRoundNext) {
                        network.failRoundNext = false;
                        throw new Error('Simulated lineup loading outage');
                    }
                    if (operation === 'validateFinalRoundEligibility' && network.failNext) {
                        network.failNext = false;
                        throw new Error('Simulated validation outage');
                    }
                    const hold = network.hold.delete(operation);
                    const response = await originalFetch(url, options);
                    if (!hold) return response;
                    return new Promise(resolve => { network.release[operation] = () => resolve(response); });
                };
            JS);
            // While the request is held, the row keeps its last answer and spins.
            $browser->click('@check-revalidate-finalRound')
                ->waitUntil('typeof window.eligibilityNetwork.release.validateFinalRoundEligibility === "function"')
                ->waitFor('[dusk="check-row-finalRound"] .mdi-spin')
                ->assertSeeIn('@check-status-finalRound', 'Fejl');
            $browser->script('window.eligibilityNetwork.release.validateFinalRoundEligibility()');
            $browser->waitUntilMissing('[dusk="check-row-finalRound"] .mdi-spin')
                ->assertSeeIn('@check-details-finalRound', $member->name);
            $browser->script('window.eligibilityNetwork.failNext = true');
            $browser->click('@check-revalidate-finalRound')
                ->waitForCheckStatus('finalRound', 'Kontrollen fejlede')
                ->assertDontSeeIn('@check-details-finalRound', $member->name)
                ->click('@check-retry-finalRound')
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->waitForCheckDetails('finalRound', $member->name);

            $browser->script('window.eligibilityNetwork.failRoundNext = true');
            $browser->click('@check-revalidate-finalRound')
                ->waitUntil('window.eligibilityNetwork.failRoundNext === false')
                ->assertSeeIn('@check-status-finalRound', 'Fejl')
                ->assertSeeIn('@check-details-finalRound', $member->name);
        });
    }

    public function test_scenario_and_shared_round_and_paper_corrections_refresh_without_reloading(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $round = TeamRound::query()->create([
            'name' => 'Scenario corrections', 'game_date' => '2026-10-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 11, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $other = Member::query()->where('gender', $member->gender)->where('refId', '!=', $member->refId)->firstOrFail();
        foreach (['Badmintonligaen', '2. division'] as $index => $tier) {
            $team = Team::factory()->withCustomTier($tier)->create([
                'name' => 'Højbjerg '.($index + 1), 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
            ]);
            $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10, 'order' => $index + 1]);
            $player = $index === 0 ? $other : $member;
            $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
                'member_ref_id' => $player->refId, 'name' => $player->name, 'gender' => $player->gender,
            ]);
        }

        $this->browse(function (Browser $browser) use ($round, $member, $other) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckDetails('finalRound', $member->name)
                ->assertDontSeeIn('@check-details-finalRound', $other->name)
                ->createScenarioFromOfficial('Uden divisionsspilleren')
                ->waitForCheckDetails('finalRound', $member->name)
                ->removeMemberFromCategory(1, '1. DS', $member->name)
                ->waitForCheckStatus('finalRound', 'OK')
                ->assertDontSeeIn('@check-details-finalRound', $member->name)
                ->selectScenario('Officiel opstilling')
                ->waitForCheckDetails('finalRound', $member->name)
                ->openSquadAction(1, 'edit-squad')
                ->waitFor('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-save-button"]')
                ->waitUntilMissing('[dusk="edit-squad-save-button"]')
                ->waitUntilMissing('@check-row-finalRound')
                ->assertCheckNotApplicable('finalRound', self::NOT_COVERED)
                ->selectScenario('Uden divisionsspilleren')
                ->assertCheckNotApplicable('finalRound', self::NOT_COVERED)
                ->assertSeeIn('[dusk="squad-1"]', 'Papirhold')
                ->assertDontSeeIn('@team-table-section', $member->name)
                ->changeRoundNumber(12)
                ->waitForCheckDetails('finalRound', $other->name)
                ->assertDontSeeIn('@check-details-finalRound', $member->name)
                ->changeRoundNumber(10)
                ->waitUntilMissing('@check-row-finalRound')
                ->assertCheckNotApplicable('finalRound', self::NOT_COVERED)
                ->selectScenario('Officiel opstilling')
                ->waitForTextIn('@team-table-section', $member->name)
                ->openSquadAction(1, 'edit-squad')
                ->waitFor('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-save-button"]')->waitUntilMissing('[dusk="edit-squad-save-button"]')
                ->changeRoundNumber(11)
                ->waitForCheckDetails('finalRound', $member->name)
                ->assertDontSeeIn('@check-details-finalRound', $other->name)
                ->assertSeeIn('@scenario-selector-dropdown .dropdown-trigger', '(Officiel)');
        });
    }

    public function test_uncertainty_names_the_historical_correction_target_without_claiming_ineligibility(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $other = Member::query()->where('gender', $member->gender)->where('refId', '!=', $member->refId)->firstOrFail();
        [$round, $team] = $this->finalRoundWithMembers(['name' => 'Final round uncertainty'], [$member, $other]);
        $earlier = $round->replicate();
        $earlier->fill(['name' => 'Efterår uden holdlink', 'game_date' => '2026-09-20', 'round' => 2])->save();
        $historical = Squad::query()->create(['team_round_id' => $earlier->id, 'name' => 'Gammelt hold', 'playerLimit' => 10]);
        $historical->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $member->refId, 'name' => $member->name, 'gender' => $member->gender,
        ]);

        $this->browse(function (Browser $browser) use ($round, $earlier, $member, $team) {
            $finding = '[dusk="check-finding-finalRound-0-'.$member->refId.'"]';
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckDetails('finalRound', 'spilleberettigelse kan ikke verificeres fuldt ud')
                ->assertSeeIn('@check-details-finalRound', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen')
                ->assertDontSeeIn($finding, 'har ikke stået på samme eller et lavere hold')
                ->assertSeeIn($finding, 'Efterår uden holdlink (runde 2)')
                ->assertAttribute('[dusk="check-correction-finalRound-'.$earlier->id.'"]', 'href', '/app/c-'.$round->clubhouse_id.'/team-fight/'.$earlier->id.'/edit')
                ->assertVisible('@check-guide-finalRound')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Saved despite uncertainty')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Saved despite uncertainty')
                ->waitForCheckDetails('finalRound', 'Efterår uden holdlink');

            $original = $browser->driver->getWindowHandle();
            $browser->click('[dusk="check-correction-finalRound-'.$earlier->id.'"]')
                ->waitUsing(5, 100, fn () => count($browser->driver->getWindowHandles()) === 2);
            $browser->driver->switchTo()->window(array_values(array_diff($browser->driver->getWindowHandles(), [$original]))[0]);
            $browser->on(new TeamFightEditPage)->waitForText('Efterår uden holdlink')
                ->openSquadAction(0, 'edit-squad')
                ->waitFor('[dusk="attach-squad-team-'.$team->id.'"]')
                ->click('[dusk="attach-squad-team-'.$team->id.'"]')
                ->click('[dusk="edit-squad-save-button"]')->waitUntilMissing('[dusk="edit-squad-save-button"]')
                ->waitForTextIn('[dusk="squad-0"]', 'Højbjerg 1');
            $browser->driver->close();
            $browser->driver->switchTo()->window($original);
            $browser->click('@check-revalidate-finalRound')
                ->waitUntilMissing($finding)
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->assertDontSeeIn('@check-details-finalRound', 'spilleberettigelse kan ikke verificeres fuldt ud')
                ->assertSeeIn('@team-table-section', $member->name)
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitUntilMissing($finding)->refresh()->on(new TeamFightEditPage)
                ->waitForCheckDetails('finalRound', 'har ikke stået på samme eller et lavere hold')
                ->assertDontSeeIn('@team-table-section', $member->name)
                ->assertDontSeeIn('@check-details-finalRound', 'spilleberettigelse kan ikke verificeres fuldt ud');
        });
    }

    public function test_an_eligibility_failure_does_not_block_saving_or_editing_the_lineup(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        [$round] = $this->finalRoundWithMembers(['name' => 'Final round eligibility'], [$member]);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckDetails('finalRound', $member->name)
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->assertSeeIn('@check-details-finalRound', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8)')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Saved despite eligibility warning')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Saved despite eligibility warning')
                ->waitForCheckDetails('finalRound', $member->name)
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitForCheckStatus('finalRound', 'OK')
                ->assertDontSeeIn('@check-details-finalRound', $member->name)
                ->refresh()->on(new TeamFightEditPage)
                ->waitFor('@team-table-section')->assertDontSeeIn('@team-table-section', $member->name);
        });
    }

    /**
     * @param  array<string, mixed>  $roundAttributes
     * @param  list<Member>  $members
     * @return array{TeamRound, Team}
     */
    private function finalRoundWithMembers(array $roundAttributes, array $members): array
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('2. division')->create([
            'name' => 'Højbjerg 1', 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create(array_merge([
            'game_date' => '2026-10-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 11, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ], $roundAttributes));
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        foreach ($members as $index => $member) {
            $squad->categories()->create(['name' => ($index + 1).'. DS', 'category' => 'DS'])->players()->create([
                'member_ref_id' => $member->refId, 'name' => $member->name, 'gender' => $member->gender,
            ]);
        }

        return [$round, $team];
    }
}
