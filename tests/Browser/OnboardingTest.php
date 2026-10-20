<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\OnboardingPage;
use Tests\DuskTestCase;

class OnboardingTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_user_without_clubhouse_opening_onboarding_is_sent_to_sign_up_finish_without_errors(): void
    {
        $user = User::factory()->create(['clubhouse_id' => null]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit(new LoginPage)
                ->type('@email', $user->email)
                ->type('@password', 'Test1234')
                ->click('@login-button')
                ->waitForLocation('/app/sign-up/finish')
                ->visit((new OnboardingPage)->url())
                ->waitForLocation('/app/sign-up/finish')
                ->waitForText('Du er ikke tilknyttet et klubhus');

            $errors = collect($browser->driver->manage()->getLog('browser'))
                ->where('level', 'SEVERE')
                ->pluck('message')
                ->filter(fn (string $message) => str_contains($message, "reading 'clubs'"));

            $this->assertEmpty($errors, $errors->implode("\n"));
        });
    }
}
