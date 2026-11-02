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

    public function test_the_check_panel_lists_every_check_in_sections_with_the_checks_that_dont_apply_folded(): void
    {
        $round = $this->roundElevenWithOnePlayer();

        $this->browse(function (Browser $browser) use ($round) {
            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightEditPage($round->clubhouse_id, $round->id))
                ->waitForCheckStatus('complete', 'Fejl')
                ->assertSeeIn('@check-section-action', 'Kræver handling (1)')
                ->assertSeeIn('@check-section-action', 'Hold fuldendt')
                ->assertSeeIn('@check-row-complete', '1 fund')
                ->waitForCheckStatus('level', 'Venter')
                ->assertSeeIn('@check-section-waiting', 'Niveau')
                ->assertSeeIn('@check-section-waiting', 'Kategori')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->assertSeeIn('@check-section-off', 'Tidligere hold')
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
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->openCheck('finalRound')
                ->assertSeeIn('@check-details-finalRound', 'Slået fra. Slå til herunder.')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Fejl')
                ->assertSeeIn('@check-section-action', 'Tidligere hold')
                ->assertSeeIn('@check-details-finalRound', 'har ikke stået på samme eller et lavere hold tidligere i sæsonen (§38 stk. 8)')
                ->switchCheck('finalRound')
                ->waitForCheckStatus('finalRound', 'Slået fra')
                ->assertDontSeeIn('@check-details-finalRound', 'har ikke stået på samme eller et lavere hold')
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
                ->waitForCheckStatus('tooYoung', 'Kontrollen fejlede')
                ->assertSeeIn('@check-section-pending', 'For ung')
                ->click('@check-retry-tooYoung')
                ->waitForCheckStatus('tooYoung', 'OK')
                ->assertMissing('@check-section-pending');
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
