<?php

namespace Tests\Browser;

use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

/**
 * Reserve Players (§43) on the seeded "Ungdom - 1. runde" (see YouthTeamRoundSeeder): Cecillie Henriksen (Hold 2 ·
 * 2. DS) plays for Nanna Reese (Hold 1 · 2. DS and 2. DD).
 */
class TeamFightReservePlayerTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    private function openRound(Browser $browser, string $name): void
    {
        $teamRound = TeamRound::where('name', $name)->sole();

        $browser->resize(1920, 1080)
            ->visit(new LoginPage)
            ->loginSPA('testing@gmail.com', 'Test1234')
            ->visit(new TeamFightEditPage($teamRound->clubhouse_id, $teamRound->id))
            ->waitForText('Holdene i holdrunden')
            ->waitForTextIn("[dusk='squad-0']", 'Nanna Reese');
    }

    public function test_the_reserve_box_opens_the_form_and_cancel_closes_it(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, 'Ungdom - 1. runde');

            $browser->assertSeeIn("[dusk='reserve-section-0']", 'Tilføj reserve til Hold 1')
                ->assertSeeIn("[dusk='reserve-section-0']", 'Kun hvis en spiller melder afbud pga. skade, sygdom eller force majeure (§43)')
                ->assertMissing("[dusk='reserve-section-0'] [dusk='reserve-form']")
                ->click("[dusk='reserve-open-0']")
                ->waitFor("[dusk='reserve-section-0'] [dusk='reserve-form']")
                ->assertSeeIn("[dusk='reserve-section-0']", 'Reserve fra et lavere hold')
                ->assertSeeIn("[dusk='reserve-section-0']", 'Erstatter (spiller på Hold 1)')
                ->assertAttribute("[dusk='reserve-section-0'] [dusk='reserve-add']", 'disabled', 'true')
                ->click("[dusk='reserve-section-0'] [dusk='reserve-cancel']")
                ->waitUntilMissing("[dusk='reserve-section-0'] [dusk='reserve-form']")
                ->assertSeeIn("[dusk='reserve-section-0']", 'Tilføj reserve til Hold 1');
        });
    }

    public function test_adding_a_reserve_shows_the_row_and_both_tags_and_removing_it_clears_them(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, 'Ungdom - 1. runde');

            $browser->addReserve(0, 'Cecillie Henriksen', 'Nanna Reese');

            $browser->assertSeeIn("[dusk='reserve-section-0']", 'Reserver på Hold 1')
                ->assertSeeIn("[dusk='reserve-section-0'] [dusk='reserve-row']", 'Fra Hold 2 · 2. DS')
                ->assertSeeIn("[dusk='squad-0'] [dusk='replaced-tag']", 'Erstattes af Cecillie Henriksen')
                ->assertSeeIn("[dusk='squad-1'] [dusk='reserve-tag']", 'Reserve på Hold 1 for Nanna Reese')
                ->waitFor('[dusk="validation-reserves"]');
            // Nanna stands on 2. DS and 2. DD; both slots get the tag.
            $this->assertCount(2, $browser->elements("[dusk='squad-0'] [dusk='replaced-tag']"));

            // The record survives a reload.
            $browser->refresh()
                ->waitForTextIn("[dusk='squad-0']", 'Nanna Reese')
                ->waitForTextIn("[dusk='reserve-section-0'] [dusk='reserve-row']", 'Cecillie Henriksen for Nanna Reese');

            $browser->click("[dusk='reserve-section-0'] [dusk='reserve-remove']")
                ->waitUntilMissing("[dusk='reserve-section-0'] [dusk='reserve-row']")
                ->assertSeeIn("[dusk='reserve-section-0']", 'Tilføj reserve til Hold 1')
                ->assertMissing("[dusk='replaced-tag']")
                ->assertMissing("[dusk='reserve-tag']");
        });
    }

    public function test_editing_a_reserve_changes_the_record_and_the_tags(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, 'Ungdom - 1. runde');
            $browser->addReserve(0, 'Cecillie Henriksen', 'Nanna Reese');

            // The form opens on the record, with its players chosen.
            $browser->click("[dusk='reserve-section-0'] [dusk='reserve-edit']")
                ->waitFor("[dusk='reserve-section-0'] [dusk='reserve-form']")
                ->assertSeeIn("[dusk='reserve-section-0'] [dusk='reserve-editing']", 'Ret Cecillie Henriksen for Nanna Reese')
                ->assertInputValue("[dusk='reserve-section-0'] input[dusk='reserve-player']", 'Cecillie Henriksen')
                ->assertInputValue("[dusk='reserve-section-0'] input[dusk='reserve-replaced']", 'Nanna Reese')
                ->click("[dusk='reserve-section-0'] [dusk='reserve-cancel']")
                ->waitUntilMissing("[dusk='reserve-section-0'] [dusk='reserve-form']");

            $browser->editReserve(0, 'Tine Gade', 'Tanja Damsgaard');

            $this->assertCount(1, $browser->elements("[dusk='reserve-section-0'] [dusk='reserve-row']"));
            $browser->assertSeeIn("[dusk='squad-0'] [dusk='replaced-tag']", 'Erstattes af Tine Gade')
                ->assertSeeIn("[dusk='squad-1'] [dusk='reserve-tag']", 'Reserve på Hold 1 for Tanja Damsgaard')
                ->assertDontSeeIn("[dusk='squad-0']", 'Erstattes af Cecillie Henriksen');
        });
    }

    public function test_the_reserve_is_tagged_with_quarantine_in_the_next_round(): void
    {
        $this->browse(function (Browser $browser) {
            $this->openRound($browser, 'Ungdom - 1. runde');
            $browser->addReserve(0, 'Cecillie Henriksen', 'Nanna Reese');

            $this->openRound($browser, 'Ungdom - 2. runde');
            $browser->waitForTextIn("[dusk='squad-1'] [dusk='reserve-quarantine-tag']", 'Karantæne · reserve i R1 på Hold 1');
        });
    }
}
