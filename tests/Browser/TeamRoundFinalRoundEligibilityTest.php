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

    public function test_the_matching_status_control_can_be_refreshed_and_disabled_only_in_the_client(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        [$round] = $this->finalRoundWithMembers(['name' => 'Client validation control'], [$member]);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $control = '.field.is-grouped > [dusk="final-round-validation-control"]';
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForTextIn('@validation-final-round', 'Deaktiveret')
                ->assertMissing('@final-round-warnings')
                ->assertDisabled('@revalidate-final-round');
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
            $browser->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->assertScript('Object.keys(window.finalRoundRequests[0]).sort().join(",")', 'scenarioId,teamRoundId')
                ->assertScript('window.finalRoundRequests[0].teamRoundId', $round->id)
                ->assertScript('window.finalRoundRequests[0].scenarioId', null)
                ->assertPresent($control.' .tags.has-addons > .tag.is-dark.is-medium')
                ->assertPresent($control.' [dusk="validation-final-round"].tag.is-medium.is-warning')
                ->assertAttribute('@revalidate-final-round', 'aria-label', 'Kontrollér tidligere hold igen')
                ->assertSeeIn('@validation-final-round', 'Advarsel')
                ->click('@revalidate-final-round')
                ->waitForTextIn('@validation-final-round', 'Advarsel')
                ->click('@toggle-final-round-check')
                ->waitForTextIn('@validation-final-round', 'Deaktiveret')
                ->assertMissing('@final-round-warnings')
                ->assertDisabled('@revalidate-final-round')
                ->changeRoundNumber(12)
                ->waitForTextIn('@validation-final-round', 'Deaktiveret')
                ->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->assertEnabled('@revalidate-final-round')
                ->refresh()
                ->waitForTextIn('@validation-final-round', 'Deaktiveret')
                ->assertDisabled('@revalidate-final-round')
                ->assertMissing('@final-round-warnings');
        });
    }

    public function test_deleting_the_selected_draft_refreshes_the_official_scenario_after_the_mutation(): void
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
            // A slow deletion and a late pre-deletion query must not leave the deleted draft in the selector.
            $browser->script(<<<'JS'
                const originalFetch = window.fetch;
                window.fetch = async function (url, options) {
                    const operation = options?.body ? JSON.parse(options.body).operationName : null;
                    if (operation === 'DeleteScenario') await new Promise(resolve => setTimeout(resolve, 300));
                    const response = await originalFetch(url, options);
                    if (operation === 'teamRound') await new Promise(resolve => setTimeout(resolve, 1000));
                    return response;
                };
            JS);
            $browser->confirmScenarioDeletion()->waitUntilMissing('@scenario-selector-dropdown')
                ->assertMissing('@final-round-validation-control')
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
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->createScenarioFromOfficial('Tomt udkast')
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitForTextIn('[dusk="validation-final-round"]', 'OK')
                ->selectScenario('Officiel opstilling')
                ->waitForTextIn('@final-round-warnings', $member->name);

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
            $browser->click('[dusk="revalidate-final-round"]')
                ->waitUntil('typeof window.eligibilityNetwork.release.validateFinalRoundEligibility === "function"')
                ->waitForTextIn('[dusk="validation-final-round"]', 'Afventer')
                ->assertMissing('@final-round-warnings');
            $browser->script('window.eligibilityNetwork.release.validateFinalRoundEligibility()');
            $browser->waitForTextIn('@final-round-warnings', $member->name);
            $browser->script('window.eligibilityNetwork.failNext = true');
            $browser->click('[dusk="revalidate-final-round"]')
                ->waitForTextIn('[dusk="validation-final-round"]', 'Fejlet')
                ->assertMissing('@final-round-warnings')
                ->click('[dusk="revalidate-final-round"]')
                ->waitForTextIn('[dusk="validation-final-round"]', 'Advarsel')
                ->waitForTextIn('@final-round-warnings', $member->name);

            $browser->script('window.eligibilityNetwork.failRoundNext = true');
            $browser->click('@revalidate-final-round')
                ->waitUntil('window.eligibilityNetwork.failRoundNext === false')
                ->assertSeeIn('@validation-final-round', 'Advarsel')
                ->assertSeeIn('@final-round-warnings', $member->name);
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
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->assertDontSeeIn('@final-round-warnings', $other->name)
                ->createScenarioFromOfficial('Uden divisionsspilleren')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->removeMemberFromCategory(1, '1. DS', $member->name)
                ->waitForTextIn('[dusk="validation-final-round"]', 'OK')
                ->assertMissing('@final-round-warnings')
                ->selectScenario('Officiel opstilling')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->openSquadAction(1, 'edit-squad')
                ->waitFor('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-save-button"]')
                ->waitUntilMissing('[dusk="edit-squad-save-button"]')
                ->waitUntilMissing('@final-round-validation-control')
                ->assertMissing('@final-round-warnings')
                ->selectScenario('Uden divisionsspilleren')
                ->assertMissing('@final-round-validation-control')
                ->assertSeeIn('[dusk="squad-1"]', 'Papirhold')
                ->assertDontSeeIn('@team-table-section', $member->name)
                ->changeRoundNumber(12)
                ->waitForTextIn('@final-round-warnings', $other->name)
                ->assertDontSeeIn('@final-round-warnings', $member->name)
                ->changeRoundNumber(10)
                ->waitUntilMissing('@final-round-validation-control')
                ->assertMissing('@final-round-warnings')
                ->selectScenario('Officiel opstilling')
                ->waitForTextIn('@team-table-section', $member->name)
                ->openSquadAction(1, 'edit-squad')
                ->waitFor('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-paper-squad-switch"]')
                ->click('[dusk="edit-squad-save-button"]')->waitUntilMissing('[dusk="edit-squad-save-button"]')
                ->changeRoundNumber(11)
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->assertDontSeeIn('@final-round-warnings', $other->name)
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
            $finding = '[dusk="final-round-finding-0-'.$member->refId.'"]';
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', 'spilleberettigelse kan ikke verificeres fuldt ud')
                ->assertSeeIn('@final-round-warnings', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen')
                ->assertDontSeeIn($finding, 'har ikke stået på samme eller et lavere hold')
                ->assertSeeIn($finding, 'Efterår uden holdlink (runde 2)')
                ->assertSeeIn($finding, 'Tilknyt det aktuelle hold')
                ->assertAttribute('[dusk="final-round-correction-'.$earlier->id.'"]', 'href', '/app/c-'.$round->clubhouse_id.'/team-fight/'.$earlier->id.'/edit')
                ->assertVisible('[dusk="final-round-team-link-guide"]')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Saved despite uncertainty')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Saved despite uncertainty')
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', 'Efterår uden holdlink');

            $original = $browser->driver->getWindowHandle();
            $browser->click('[dusk="final-round-correction-'.$earlier->id.'"]')
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
            $browser->click('[dusk="revalidate-final-round"]')
                ->waitUntilMissing($finding)
                ->waitForTextIn('[dusk="validation-final-round"]', 'Advarsel')
                ->assertDontSeeIn('@final-round-warnings', 'spilleberettigelse kan ikke verificeres fuldt ud')
                ->assertSeeIn('@team-table-section', $member->name)
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitUntilMissing($finding)->refresh()->on(new TeamFightEditPage)
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', 'har ikke stået på samme eller et lavere hold')
                ->assertDontSeeIn('@team-table-section', $member->name)
                ->assertDontSeeIn('@final-round-warnings', 'spilleberettigelse kan ikke verificeres fuldt ud');
        });
    }

    public function test_eligibility_warning_does_not_block_saving_or_editing_the_lineup(): void
    {
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        [$round] = $this->finalRoundWithMembers(['name' => 'Final round eligibility'], [$member]);

        $this->browse(function (Browser $browser) use ($round, $member) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->waitForTextIn('[dusk="validation-final-round"]', 'Advarsel')
                ->assertSeeIn('@final-round-warnings', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8)')
                ->click('@settings-button')->waitFor('@settings-name')
                ->type('@settings-name', 'Saved despite eligibility warning')->click('@settings-save')
                ->waitUntilMissing('@settings-modal')->refresh()->on(new TeamFightEditPage)
                ->waitForText('Saved despite eligibility warning')
                ->waitFor('@toggle-final-round-check')->click('@toggle-final-round-check')
                ->waitForTextIn('@final-round-warnings', $member->name)
                ->removeMemberFromCategory(0, '1. DS', $member->name)
                ->waitForTextIn('[dusk="validation-final-round"]', 'OK')
                ->waitUntilMissing('@final-round-warnings')->refresh()->on(new TeamFightEditPage)
                ->waitFor('@team-table-section')->assertDontSeeIn('@team-table-section', $member->name)
                ->assertMissing('@final-round-warnings');
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
