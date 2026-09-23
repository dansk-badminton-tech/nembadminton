<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HelpGuidePage extends Page
{
    public function __construct(private string $slug)
    {
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/app/help/guides/' . $this->slug;
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->waitFor('@help-article');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@help-article' => "[dusk='help-article']",
        ];
    }
}
