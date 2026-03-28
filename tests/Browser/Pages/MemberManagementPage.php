<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class MemberManagementPage extends Page
{
    private $clubhouseId;

    public function __construct($clubhouseId)
    {
        $this->clubhouseId = $clubhouseId;
    }

    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return "/app/c-{$this->clubhouseId}/members";
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertSee('Spillere i klubhuset');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@search-input' => 'input[placeholder="Indtast navn..."]',
            '@gender-select' => '.field:has(label:contains("Køn")) select',
            '@show-inactive-toggle' => '.field:has(label:contains("Vis inaktive")) .switch',
            '@members-table' => 'table',
            '@info-message' => '.message.is-info',
        ];
    }

    /**
     * Search for a member by name
     */
    public function searchMember(Browser $browser, string $name): self
    {
        $browser->type('@search-input', $name)
                ->pause(500); // Wait for debounce
        return $this;
    }

    /**
     * Filter by gender
     */
    public function filterByGender(Browser $browser, string $gender): self
    {
        $browser->select('@gender-select', $gender);
        return $this;
    }

    /**
     * Toggle show inactive members
     */
    public function toggleShowInactive(Browser $browser): self
    {
        $browser->click('@show-inactive-toggle');
        return $this;
    }

    /**
     * Toggle inactive status for a member
     */
    public function toggleMemberInactiveStatus(Browser $browser, string $memberName): self
    {
        $browser->waitForText($memberName)
                ->with('table tbody', function ($table) use ($memberName) {
                    $table->assertSee($memberName)
                          ->clickLink('Marker som inaktiv');
                });
        return $this;
    }

    /**
     * Assert member is visible in table
     */
    public function assertMemberVisible(Browser $browser, string $memberName): self
    {
        $browser->with('@members-table', function ($table) use ($memberName) {
            $table->assertSee($memberName);
        });
        return $this;
    }

    /**
     * Assert member has specific status
     */
    public function assertMemberStatus(Browser $browser, string $memberName, string $status): self
    {
        $browser->with('@members-table', function ($table) use ($memberName, $status) {
            $table->assertSee($memberName)
                  ->assertSee($status);
        });
        return $this;
    }
}
