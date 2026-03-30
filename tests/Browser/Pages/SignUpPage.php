<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class SignUpPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        // SPA is served under /app; router path is /sign-up
        return '/app/sign-up';
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
            '@form' => "[dusk='sign-up-form']",
            '@name-input' => "[dusk='name-input']",
            '@email-input' => "[dusk='email-input']",
            '@password-input' => "[dusk='password-input']",
            '@password-confirmation-input' => "[dusk='password-confirmation-input']",
            '@term-checkbox' => "[dusk='term-checkbox']",
            '@signup-button' => "[dusk='signup-button']",
            '@login-link' => "a[href='/login']",
        ];
    }

    /**
     * Fill and submit the sign up form.
     */
    public function signUp(Browser $browser, string $name, string $email, string $password): void
    {
        $browser->type('@name-input', $name)
            ->type('@email-input', $email)
            ->type('@password-input', $password)
            ->type('@password-confirmation-input', $password)
            ->check('@term-checkbox')
            ->click('@signup-button');
    }

}
