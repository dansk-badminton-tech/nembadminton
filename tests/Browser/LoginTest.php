<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    /**
     * A Dusk test example.
     */
    public function test_example(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/app/login')
                ->assertSee('Login');
        });
    }

    public function test_login_returns_to_the_deep_link_the_user_opened(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/app/c-1/team-fight/dashboard')
                ->on(new LoginPage)
                ->assertQueryStringHas('redirect', '/c-1/team-fight/dashboard')
                ->type('@email', 'testing@gmail.com')
                ->type('@password', 'Test1234')
                ->click('@login-button')
                ->waitForLocation('/app/c-1/team-fight/dashboard')
                ->assertPathIs('/app/c-1/team-fight/dashboard');
        });
    }

    public function test_login_without_redirect_goes_home(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->assertPathIs('/app/c-1/home');
        });
    }

    public function test_login_ignores_a_redirect_to_another_site(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/app/login?redirect='.urlencode('//example.com/phish'))
                ->on(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->assertPathIs('/app/c-1/home');
        });
    }
}
