<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\ClubDashboardPage;
use Tests\Browser\Pages\OnboardingPage;
use Tests\Browser\Pages\SignUpFinishPage;
use Tests\Browser\Pages\SignUpPage;
use Tests\DuskTestCase;

class SignUpTest extends DuskTestCase
{

    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    /**
     * A Dusk test example.
     */
    public function testSignUp(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new SignUpPage())
                    ->signUp('Daniel Fly Nygaard', 'daniel@gmail.com', 'Test1234')
                    ->on(new SignUpFinishPage())
                    ->createClubhouse('Mit klubhus', 'daniel@gmail.com', 1087)
                    ->on(new OnboardingPage())
                    ->assertSee("Importer data fra badmintonplayer.dk...")
                    ->waitForText('Importering færdig. Sender dig til dashboard om 3 sekunder', 30)
                    ->on(new ClubDashboardPage())
                    ->assertSee("Mit klubhus")
                    ->assertSee("Daniel Fly Nygaard")
                    ->screenshot('signup-complete');
        });
    }
}
