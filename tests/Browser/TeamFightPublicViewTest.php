<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\Browser\Pages\TeamFightEditPage;
use Tests\Browser\Pages\TeamFightPublicPage;
use Tests\DuskTestCase;

class TeamFightPublicViewTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    public function test_public_link_shows_only_the_official_lineup_before_and_after_promotion(): void
    {
        $publicUrl = null;
        $editorUrl = null;

        $this->browse(function (Browser $admin) use (&$publicUrl, &$editorUrl) {
            $createPage = new TeamFightCreatePage(Clubhouse::firstOrFail()->id);
            $admin->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($createPage)
                ->on($createPage)
                ->waitUntilEnabled('@name-input')
                ->type('@name-input', 'Public sharing journey')
                ->setRound(1)
                ->selectDate(7, 2025, 14)
                ->selectRankingByText('Juli 2025')
                ->click('@submit-button')
                ->waitForText('Dit hold er gemt')
                ->on(new TeamFightEditPage);

            $admin->addCustomSquad('Sharing Squad', 'Kredsserie', ['womenSingles' => 1])
                ->waitForTextIn("[dusk='squad-0']", '1. DS')
                ->fillCategorySlot(0, '1. DS', 'Spela Silvester Laumand')
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand');
            $editorUrl = $admin->driver->getCurrentURL();
            $admin->click('@share-button')
                ->click('@share-link-option')
                ->waitFor('@share-modal')
                ->assertSeeIn('@share-modal', 'Du behøver ikke at være logget ind');

            $publicUrl = $admin->attribute('@public-link', 'href');
            $publicPath = parse_url($publicUrl, PHP_URL_PATH);
            $this->assertMatchesRegularExpression('~^/app/team-fight/[^/]+/public-view$~', $publicPath);
            $this->assertStringContainsString($publicPath, $admin->text('@share-modal'));

            $editorWindow = $admin->driver->getWindowHandle();
            $admin->click('@public-link')
                ->waitUsing(10, 100, fn () => count($admin->driver->getWindowHandles()) === 2);
            $publicWindow = array_values(array_diff($admin->driver->getWindowHandles(), [$editorWindow]))[0];
            $admin->driver->switchTo()->window($publicWindow);
            $admin->on(new TeamFightPublicPage)
                ->assertPathIs($publicPath)
                ->assertSeeIn('@title', 'Public sharing journey');
            $admin->driver->close();
            $admin->driver->switchTo()->window($editorWindow);

            $admin->on(new TeamFightEditPage)
                ->click("[dusk='team-round-share-modal'] .card-footer a:last-child")
                ->waitUntilMissing('@share-modal')
                ->createScenarioFromOfficial('Plan B')
                ->removeSquadMember(0, 'Spela Silvester Laumand')
                ->fillCategorySlot(0, '1. DS', 'Michella Skov')
                ->waitForTextIn('@team-table-section', 'Michella Skov');
        });

        // Dusk reuses its primary browser across browse calls unless explicitly closed.
        static::closeAll();
        $this->browse(function (Browser $viewer) use (&$publicUrl) {
            $viewer->visit($publicUrl)
                ->on(new TeamFightPublicPage);
            $this->assertNull($viewer->script("return localStorage.getItem('access_token')")[0]);
            $viewer
                ->assertSeeIn('@title', 'Public sharing journey')
                ->assertPresent('@game-date')
                ->assertSeeIn("[dusk='squad-card-0']", 'SHARING SQUAD')
                ->assertSeeIn("[dusk='squad-card-0']", 'Kredsserie')
                ->assertSeeIn("[dusk='squad-card-0'] .category-label", '1. DS')
                ->assertSeeIn("[dusk='player-870114-15']", 'Spela Silvester Laumand')
                ->assertMissing("[dusk='player-910128-22']")
                ->assertMissing('@not-found');
        });

        static::closeAll();
        $this->browse(function (Browser $admin) use (&$editorUrl) {
            $admin->visit(new LoginPage)
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit($editorUrl)
                ->on(new TeamFightEditPage)
                ->selectScenario('Plan B')
                ->assertScenarioSelected('Plan B', false)
                ->confirmScenarioPromotion()
                ->assertScenarioSelected('Plan B', true)
                ->selectScenario('Public sharing journey')
                ->assertScenarioSelected('Public sharing journey', false)
                ->waitForTextIn('@team-table-section', 'Spela Silvester Laumand');
        });

        static::closeAll();
        $this->browse(function (Browser $viewer) use (&$publicUrl) {
            $viewer->visit($publicUrl)
                ->on(new TeamFightPublicPage);
            $this->assertNull($viewer->script("return localStorage.getItem('access_token')")[0]);
            $viewer
                ->assertSeeIn('@title', 'Public sharing journey')
                ->assertSeeIn("[dusk='squad-card-0']", 'SHARING SQUAD')
                ->assertSeeIn("[dusk='squad-card-0'] .category-label", '1. DS')
                ->assertSeeIn("[dusk='player-910128-22']", 'Michella Skov')
                ->assertMissing("[dusk='player-870114-15']");
        });
    }

    public function test_public_team_fight_link_shows_not_found_for_unknown_id(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new TeamFightPublicPage('does-not-exist'))
                ->on(new TeamFightPublicPage('does-not-exist'))
                ->assertPresent('@not-found');
        });
    }
}
