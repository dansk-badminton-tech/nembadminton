<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\DuskTestCase;

class TeamFightConstruct13KampsTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_user_can_construct_holdrunde_with_13_kamps_hold(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // Step 1: Login
            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234');

            // Step 2: Create holdrunde
            $createPage = new TeamFightCreatePage($clubhouse->id);
            $browser->visit($createPage)
                ->on($createPage);

            $browser->waitUntilEnabled('@name-input')
                ->type('@name-input', '13 Kamps Holdrunde Test');

            $browser->on($createPage)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025');

            // Submit and wait for success
            $browser->click('@submit-button')
                ->waitForText('Dit hold er gemt');

            // Verify redirect to edit page
            $browser->assertPathContains('/team-fight/')
                ->assertPathContains('/edit')
                ->waitForText('Holdene i holdrunden')
                ->assertSee('13 Kamps Holdrunde Test');

            // Register TeamFightEditPage macros so we can call page object methods
            $browser->on(new TeamFightEditPage());

            // Step 3: Add 13-kamps hold
            $browser->add13KampsHold();

            // Verify Hold 1 appears with all 13 categories
            $browser->waitForText('Hold 1')
                ->assertSee('1. MD')
                ->assertSee('2. MD')
                ->assertSee('1. DS')
                ->assertSee('2. DS')
                ->assertSee('1. HS')
                ->assertSee('2. HS')
                ->assertSee('3. HS')
                ->assertSee('4. HS')
                ->assertSee('1. DD')
                ->assertSee('2. DD')
                ->assertSee('1. HD')
                ->assertSee('2. HD')
                ->assertSee('3. HD');

            // Step 4: Add 10 unique players from the ranking list panel
            //   1. Dame Mix  → 2 women → auto-placed into 1.MD, 2.MD (first slot)
            //   2. Herre Mix → 2 men   → auto-placed into 1.MD, 2.MD (second slot)
            //   3. Dame Single → 2 women → auto-placed into 1.DS, 2.DS
            //   4. Herre Single → 4 men  → auto-placed into 1.HS–4.HS
            //   5. DD 1 & 2 → inline search with existing dame players
            //   6. HD 1, 2, 3 → inline search with existing herre players

            $browser->script("window.scrollTo(0, 0)");

            // --- 1. Dame Mix: add 2 women ---
            $browser->switchRankingList('WOMEN_MIX');
            $browser->addPlayersFromRankingList(2);

            // --- 2. Herre Mix: add 2 men ---
            $browser->switchRankingList('MEN_MIX');
            $browser->addPlayersFromRankingList(2);

            // --- 3. Dame Single: add 2 women ---
            $browser->switchRankingList('WOMEN_SINGLE');
            $browser->addPlayersFromRankingList(2);

            // --- 4. Herre Single: add 4 men ---
            $browser->switchRankingList('MEN_SINGLE');
            $browser->addPlayersFromRankingList(4);

            $browser->screenshot('13kamps-10-players-added');

            // --- 5. DD 1 & 2: assign existing dame players via inline search (4 slots) ---
            $browser->scrollTo("[dusk='team-table-section']");
            for ($i = 0; $i < 4; $i++) {
                $browser->fillNextInlineSlot();
            }

            $browser->screenshot('13kamps-dd-filled');

            // --- 6. HD 1, 2, 3: assign existing herre players via inline search (6 slots) ---
            for ($i = 0; $i < 6; $i++) {
                $browser->fillNextInlineSlot();
            }

            $browser->screenshot('13kamps-hd-filled');

            // Step 5: Verify all categories are populated
            $browser->assertSee('Hold 1')
                ->assertSee('1. MD')
                ->assertSee('2. MD')
                ->assertSee('1. DS')
                ->assertSee('2. DS')
                ->assertSee('1. HS')
                ->assertSee('2. HS')
                ->assertSee('3. HS')
                ->assertSee('4. HS')
                ->assertSee('1. DD')
                ->assertSee('2. DD')
                ->assertSee('1. HD')
                ->assertSee('2. HD')
                ->assertSee('3. HD');

            $browser->assertAllSlotsFilled();

            // Step 6: Verify validation status indicators
            $browser->assertValidationPassing();

            $browser->screenshot('13kamps-test-complete');
        });
    }
}
