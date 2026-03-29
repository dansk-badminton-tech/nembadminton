<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class FaqPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/faq';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitFor('@faq-page');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@faq-page' => "[dusk='faq-page']",
            '@faq-content-section' => "[dusk='faq-content-section']",
        ];
    }
}
