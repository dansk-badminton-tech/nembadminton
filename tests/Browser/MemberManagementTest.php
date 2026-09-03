<?php

namespace Tests\Browser;

use App\Models\Member;
use App\Models\User;
use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\MemberManagementPage;
use Tests\Browser\Pages\LoginPage;
use Tests\DuskTestCase;

class MemberManagementTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    /**
     * Test that member management page loads and displays members
     */
    public function testMemberManagementPageLoads(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->assertSee('Spillere i klubhuset')
                    ->assertSee('Om spillere:')
                    ->assertSee('badmintonplayer.dk API')
                    ->assertVisible('@members-table');
        });
    }

    /**
     * Test searching for members by name
     */
    public function testSearchMembersByName(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $member = Member::whereHas('clubs', function ($query) use ($clubhouse) {
                $query->where('club_id', $clubhouse->clubs->first()->id);
            })->first();

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->waitForText($member->name)
                    ->searchMember($member->name)
                    ->waitFor('@members-table')
                    ->assertMemberVisible($member->name);
        });
    }

    /**
     * Test filtering members by gender
     */
    public function testFilterMembersByGender(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->waitFor('@members-table')
                    ->filterByGender('MEN')
                    ->pause(1000)
                    ->with('@members-table', function ($table) {
                        $table->assertSee('Herre');
                    });
        });
    }

    /**
     * Test toggling member playable status (midlertidigt utilgængelig)
     */
    public function testToggleMemberPlayableStatus(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // Get an active and playable member
            $member = Member::whereHas('clubs', function ($query) use ($clubhouse) {
                $query->where('club_id', $clubhouse->clubs->first()->id);
            })->where('inactive', false)->where('playable', true)->first();

            if (!$member) {
                $this->markTestSkipped('No active playable member found for testing');
            }

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->waitForText($member->name)
                    ->assertMemberStatus($member->name, 'Aktiv')
                    ->toggleMemberPlayableStatusById($member->id)
                    ->waitForText('Spiller markeret som midlertidigt utilgængelig')
                    ->pause(1000)
                    ->assertMemberStatus($member->name, 'Midlertidigt utilgængelig');

            // Verify the member was marked as unplayable in database
            $this->assertFalse(
                (bool) Member::find($member->id)->playable,
                'Member should be marked as unplayable in database'
            );

            // Toggle back to playable
            $browser->toggleMemberPlayableStatusById($member->id)
                    ->waitForText('Spiller markeret som tilgængelig')
                    ->pause(1000)
                    ->assertMemberStatus($member->name, 'Aktiv');

            $this->assertTrue(
                (bool) Member::find($member->id)->playable,
                'Member should be marked as playable in database'
            );
        });
    }

    /**
     * Test toggling show inactive members filter
     */
    public function testToggleShowInactiveMembers(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // Create an inactive member for testing
            $inactiveMember = Member::whereHas('clubs', function ($query) use ($clubhouse) {
                $query->where('club_id', $clubhouse->clubs->first()->id);
            })->first();

            if ($inactiveMember) {
                $inactiveMember->update(['inactive' => true]);
            }

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->waitFor('@members-table')
                    // Inactive members should not be visible by default
                    ->assertDontSee($inactiveMember->name)
                    ->toggleShowInactive()
                    ->pause(1000)
                    // Now inactive members should be visible
                    ->assertSee($inactiveMember->name)
                    ->assertMemberStatus($inactiveMember->name, 'Inaktiv');
        });
    }

    /**
     * Test that information message explains the feature correctly
     */
    public function testInformationMessageIsDisplayed(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->assertSee('Om spillere:')
                    ->assertSee('badmintonplayer.dk API')
                    ->assertSee('Forskel på "Inaktiv" og "Midlertidigt utilgængelig"')
                    ->assertSee('Denne status er styret af Badmintonplayer')
                    ->assertSee('Midlertidigt utilgængelig');
        });
    }
}
