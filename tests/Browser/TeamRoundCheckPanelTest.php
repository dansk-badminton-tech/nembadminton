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

class TeamRoundCheckPanelTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_checks_run_when_returning_to_an_editor_with_a_cached_query_result(): void
    {
        $round = $this->roundElevenWithOnePlayer();
        $path = "/c-{$round->clubhouse_id}/team-fight/{$round->id}/edit";

        $this->browse(function (Browser $browser) use ($round, $path) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('complete', 'Fejl');
            $browser->script(<<<'JS'
                const app = document.querySelector('#app').__vue_app__;
                const original = app.config.errorHandler;
                window.checkStartupErrors = [];
                app.config.errorHandler = (error, ...args) => {
                    window.checkStartupErrors.push(error.message);
                    original?.(error, ...args);
                };
            JS);
            $browser->script('document.querySelector("#app").__vue_app__.config.globalProperties.$router.push('.json_encode("/c-{$round->clubhouse_id}/team-fight").')');
            $browser->waitUntilMissing('@check-panel');
            // SPA navigation preserves Apollo's cache; a page reload would hide the bug.
            $browser->script('document.querySelector("#app").__vue_app__.config.globalProperties.$router.push('.json_encode($path).')');
            $browser->waitFor('@check-panel')
                ->assertScript('JSON.stringify(window.checkStartupErrors)', '[]')
                ->waitForCheckStatus('complete', 'Fejl')
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->assertMissing('@check-section-pending');
        });
    }

    public function test_the_check_panel_lists_every_check_in_sections_with_the_checks_that_dont_apply_folded(): void
    {
        $round = $this->roundElevenWithOnePlayer();

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('complete', 'Fejl')
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->assertSeeIn('@check-section-action', 'Kræver handling (2)')
                ->assertSeeIn('@check-section-action', 'Hold fuldendt')
                ->assertSeeIn('@check-section-action', 'Tidligere hold')
                ->assertSeeIn('@check-row-complete', '1 fund')
                ->waitForCheckStatus('level', 'Venter')
                ->assertSeeIn('@check-section-waiting', 'Niveau')
                ->assertSeeIn('@check-section-waiting', 'Kategori')
                ->assertMissing('@check-section-off')
                ->waitForCheckStatus('tooYoung', 'OK')
                ->assertSeeIn('@check-section-ok', 'For ung')
                ->assertSeeIn('@check-row-tooYoung', 'pr. spiller')
                ->waitForTextIn('@check-not-applicable', '4 tjek gælder ikke for denne opstilling')
                ->assertMissing('@check-not-applicable-reserves')
                ->assertCheckNotApplicable('qualification', 'Gælder kun runde 12 i 2.–3. division og Danmarksserien.')
                ->assertCheckNotApplicable('reserves', 'Opstillingen har ingen reserver.')
                ->assertCheckNotApplicable('paperSquads', 'Opstillingen har intet papirhold.')
                ->assertCheckNotApplicable('playoff', 'Gælder kun 1. division og Badmintonligaen.');
        });
    }

    public function test_a_row_starts_folded_and_unfolds_to_what_the_check_tests_and_its_findings(): void
    {
        $round = $this->roundElevenWithOnePlayer();

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('complete', 'Fejl')
                ->assertMissing('@check-details-complete')
                ->assertSeeIn('@check-row-complete', 'Detaljer')
                ->click('@check-row-complete')
                ->waitFor('@check-details-complete')
                ->assertSeeIn('@check-details-complete', 'Alle hold har en spiller på hver plads.')
                ->assertSeeIn('@check-details-complete', 'Hold 1 mangler spillere.')
                ->click('@check-row-complete')
                ->waitUntilMissing('@check-details-complete')
                ->openCheck('level')
                ->assertSeeIn('@check-details-level', 'Venter på, at alle hold er fuldt besat.');
        });
    }

    public function test_a_check_is_switched_off_and_on_inside_its_unfolded_row(): void
    {
        $round = $this->roundElevenWithOnePlayer();

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->openCheck('finalRound')
                ->assertSeeIn('@check-section-action', 'Tidligere hold')
                ->assertSeeIn('@check-details-finalRound', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8)')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->assertSeeIn('@check-section-off', 'Tidligere hold')
                ->assertSeeIn('@check-details-finalRound', 'Slå tjekket til herunder.')
                ->assertDontSeeIn('@check-details-finalRound', 'har ikke stået på samme eller et lavere hold')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                // With Hold fuldendt switched off, Niveau and Kategori run on what's there.
                ->switchCheck('complete')
                ->waitForCheckStatus('complete', 'Slået fra')
                ->waitForCheckStatus('level', 'OK')
                ->waitForCheckStatus('category', 'OK')
                ->switchCheck('complete')
                ->waitForCheckStatus('complete', 'Fejl')
                ->waitForCheckStatus('level', 'Venter');
        });
    }

    public function test_a_switched_off_check_stays_off_after_a_reload_and_in_a_scenario_of_the_same_round(): void
    {
        $round = $this->roundElevenWithOnePlayer();

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                // Tidligere hold starts on.
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->refresh()->on(new TeamFightEditPage)
                ->waitForCheckStatus('complete', 'Fejl')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->createScenarioFromOfficial('Udkast med fravalg')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->selectScenario('Officiel opstilling')
                ->waitForCheckStatus('finalRound', 'Fejl');
        });
    }

    public function test_a_failed_request_offers_to_try_again(): void
    {
        $round = $this->roundElevenWithOnePlayer();

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('tooYoung', 'OK');
            $browser->script(<<<'JS'
                const originalFetch = window.fetch;
                window.failTooYoung = true;
                window.fetch = function (url, options) {
                    const operation = options?.body ? JSON.parse(options.body).operationName : null;
                    if (operation === 'validateTooYoungPlayers' && window.failTooYoung) {
                        window.failTooYoung = false;
                        return Promise.reject(new Error('Simulated outage'));
                    }
                    return originalFetch(url, options);
                };
            JS);
            // Switching Hold fuldendt off runs the Checks again; For ung's request fails once.
            $browser->switchCheck('complete')
                ->waitForCheckStatus('tooYoung', 'Tjekket kunne ikke køre')
                ->assertSeeIn('@check-section-pending', 'For ung')
                ->click('@check-retry-tooYoung')
                ->waitForCheckStatus('tooYoung', 'OK')
                ->assertMissing('@check-section-pending');
        });
    }

    public function test_kategori_cannot_be_verified_while_a_player_has_no_points_in_their_category(): void
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('2. division')->create([
            'name' => 'Højbjerg 1', 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create([
            'name' => 'Ingen point', 'game_date' => '2026-10-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 4, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        $withPoints = Member::query()->where('refId', '870114-15')->firstOrFail();
        $withoutPoints = Member::query()->where('refId', '010204-01')->firstOrFail();
        $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $withPoints->refId, 'name' => $withPoints->name, 'gender' => $withPoints->gender,
        ])->points()->create(['category' => 'DS', 'points' => 2000, 'position' => 1, 'version' => '2025-07-02']);
        $squad->categories()->create(['name' => '2. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $withoutPoints->refId, 'name' => $withoutPoints->name, 'gender' => $withoutPoints->gender,
        ]);

        $this->browse(function (Browser $browser) use ($round, $withoutPoints) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('category', 'Kan ikke kontrolleres')
                ->assertSeeIn('@check-section-action', 'Kategori')
                ->openCheck('category')
                ->assertSeeIn('@check-details-category', 'Kategori kan først kontrolleres, når alle spillere har point i de kategorier, de spiller.')
                ->assertSeeIn("@check-finding-category-0-{$withoutPoints->refId}",
                    "{$withoutPoints->name} har ingen point i DS.");
        });
    }

    private function roundElevenWithOnePlayer(): TeamRound
    {
        $clubhouse = Clubhouse::firstOrFail();
        $team = Team::factory()->withCustomTier('2. division')->create([
            'name' => 'Højbjerg 1', 'clubhouse_id' => $clubhouse->id, 'season_id' => 2026,
        ]);
        $round = TeamRound::query()->create([
            'name' => 'Tjekpanel', 'game_date' => '2026-10-11', 'version' => '2025-07-02',
            'season_id' => 2026, 'round' => 11, 'clubhouse_id' => $clubhouse->id,
            'user_id' => User::query()->where('email', 'testing@gmail.com')->value('id'),
        ]);
        $member = Member::query()->where('refId', '870114-15')->firstOrFail();
        $squad = Squad::query()->create(['team_round_id' => $round->id, 'team_id' => $team->id, 'playerLimit' => 10]);
        $squad->categories()->create(['name' => '1. DS', 'category' => 'DS'])->players()->create([
            'member_ref_id' => $member->refId, 'name' => $member->name, 'gender' => $member->gender,
        ]);
        // An empty place, so Hold fuldendt fails.
        $squad->categories()->create(['name' => '1. DD', 'category' => 'DD']);

        return $round;
    }
}
