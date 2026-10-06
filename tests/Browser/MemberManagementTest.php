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
     * Test toggling member playable status (permanent afbud)
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
                    ->waitForText('Permanent afbud registreret')
                    ->pause(1000)
                    ->assertMemberStatus($member->name, 'Permanent afbud');

            // Verify the member was marked as unplayable in database
            $this->assertFalse(
                (bool) Member::find($member->id)->playable,
                'Member should be marked as unplayable in database'
            );

            // Toggle back to playable
            $browser->toggleMemberPlayableStatusById($member->id)
                    ->waitForText('Permanent afbud annulleret')
                    ->pause(1000)
                    ->assertMemberStatus($member->name, 'Aktiv');

            $this->assertTrue(
                (bool) Member::find($member->id)->playable,
                'Member should be marked as playable in database'
            );
        });
    }

    /**
     * Test toggling member inactive status override
     */
    public function testToggleMemberInactiveOverride(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            // Get an active member
            $member = Member::whereHas('clubs', function ($query) use ($clubhouse) {
                $query->where('club_id', $clubhouse->clubs->first()->id);
            })->where('inactive', false)->first();

            if (!$member) {
                $this->markTestSkipped('No active member found for testing');
            }

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->filterByStatus('alle')
                    ->waitForText($member->name)
                    ->assertMemberStatus($member->name, 'Aktiv')
                    ->toggleMemberInactiveStatusById($member->id)
                    ->waitForText('Spiller markeret som inaktiv')
                    ->assertMemberStatus($member->name, 'Inaktiv')
                    ->assertSee('Tilsidesat');

            // Verify in database: marked as inactive and FORCE_INACTIVE
            $dbMember = Member::find($member->id);
            $this->assertTrue((bool) $dbMember->inactive);
            $this->assertEquals('FORCE_INACTIVE', $dbMember->override_inactive);

            // Toggle back to active
            $browser->toggleMemberInactiveStatusById($member->id)
                    ->waitForText('Spiller markeret som aktiv')
                    ->assertMemberStatus($member->name, 'Aktiv')
                    ->assertSee('Tilsidesat');

            $dbMember = Member::find($member->id);
            $this->assertFalse((bool) $dbMember->inactive);
            $this->assertEquals('FORCE_ACTIVE', $dbMember->override_inactive);
        });
    }

    /**
     * Test that inactive members are hidden by default and shown under "Inaktive"
     */
    public function testFilterInactiveMembers(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            [$inactiveMember, $activeMember] = $this->activePlayableMembers($clubhouse, 2)->all();
            $inactiveMember->update(['inactive' => true]);

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->searchMember($inactiveMember->name)
                    ->waitForTextIn('@members-empty', 'Ingen aktive spillere matcher')
                    ->filterByStatus('inaktive')
                    ->assertMemberStatus($inactiveMember->name, 'Inaktiv')
                    ->searchMember($activeMember->name)
                    ->waitForTextIn('@members-empty', 'Ingen inaktive spillere matcher');
        });
    }

    /**
     * Test showing only members with permanent afbud
     */
    public function testFilterPermanentCancellations(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            [$cancelledMember, $playableMember] = $this->activePlayableMembers($clubhouse, 2)->all();
            $cancelledMember->update(['playable' => false]);

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->searchMember($playableMember->name)
                    ->waitForTextIn('@members-table', $playableMember->name)
                    ->filterByStatus('afbud')
                    ->waitForTextIn('@members-empty', 'Ingen spillere med permanent afbud matcher')
                    ->searchMember($cancelledMember->name)
                    ->assertMemberStatus($cancelledMember->name, 'Permanent afbud');
        });
    }

    /**
     * Test that the filters are kept in the URL and restored from it
     */
    public function testFiltersAreKeptInUrl(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $member = $this->activePlayableMembers($clubhouse, 1)->first();
            $member->update(['playable' => false]);

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->filterByStatus('afbud')
                    ->searchMember($member->name)
                    ->assertMemberStatus($member->name, 'Permanent afbud')
                    ->assertQueryStringHas('status', 'afbud')
                    ->assertQueryStringHas('q', $member->name)
                    ->refresh()
                    ->waitForTextIn('@members-table', $member->name)
                    ->assertInputValue('@search-input', $member->name)
                    ->assertMemberStatus($member->name, 'Permanent afbud');
        });
    }

    /**
     * Test that "Vis alle spillere" in the empty state clears the filters
     */
    public function testShowAllMembersFromEmptyState(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                    ->loginSPA('testing@gmail.com', 'Test1234')
                    ->visit(new MemberManagementPage($clubhouse->id))
                    ->filterByStatus('inaktive')
                    ->searchMember('ingen spiller hedder sådan')
                    ->waitForTextIn('@members-empty', 'Ingen inaktive spillere matcher')
                    ->click('@show-all-members')
                    ->waitUntilMissing('@members-empty')
                    ->assertInputValue('@search-input', '')
                    ->assertQueryStringHas('status', 'alle')
                    ->assertQueryStringMissing('q');
        });
    }

    /**
     * @return \Illuminate\Support\Collection<int, Member>
     */
    private function activePlayableMembers(Clubhouse $clubhouse, int $count)
    {
        $members = Member::whereHas('clubs', function ($query) use ($clubhouse) {
            $query->where('club_id', $clubhouse->clubs->first()->id);
        })->where('inactive', false)->where('playable', true)->orderBy('name')->take($count)->get();

        if ($members->count() < $count) {
            $this->markTestSkipped("Need {$count} active playable members for testing");
        }

        return $members;
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
                    ->assertSee('Forskel på "Inaktiv" og "Permanent afbud"')
                    ->assertSee('Denne status er styret af Badmintonplayer')
                    ->assertSee('Spilleren kan ikke vælges i nogen holdrunde');
        });
    }
}
