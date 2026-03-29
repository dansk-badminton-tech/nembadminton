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
            '@search-input' => '[dusk="search-name-input"]',
            '@gender-select' => '[dusk="gender-select"]',
            '@show-inactive-switch' => '[dusk="show-inactive-switch"]',
            '@members-table' => '[dusk="member-management-card"]',
            '@info-message' => '[dusk="info-message"]',
            '@card' => '[dusk="member-management-card"]',
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
        $browser->click('@show-inactive-switch');
        return $this;
    }

    /**
     * Toggle inactive status for a member by member ID
     */
    public function toggleMemberInactiveStatusById(Browser $browser, int $memberId): self
    {
        $browser->click("[dusk='toggle-inactive-{$memberId}']");
        return $this;
    }

    /**
     * Toggle inactive status for a member by name (fallback method)
     */
    public function toggleMemberInactiveStatus(Browser $browser, string $memberName): self
    {
        $browser->waitForText($memberName)
                ->with('@members-table tbody', function ($table) use ($memberName) {
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
