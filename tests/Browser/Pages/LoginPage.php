<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class LoginPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/login';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitFor('@form');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@form' => "[dusk='login-form']",
            '@email' => "[dusk='email-input']",
            '@password' => "[dusk='password-input']",
            '@login-button' => "[dusk='login-button']",
        ];
    }

    /**
     * Log in to the application.
     */
    public function loginSPA(Browser $browser, string $email, string $password): void
    {
        $browser->type('@email', $email)
            ->type('@password', $password)
            ->click('@login-button')
            ->waitForLocation('/app/home-redirect');
    }
}
