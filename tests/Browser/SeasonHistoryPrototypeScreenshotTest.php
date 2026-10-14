<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use App\Models\TeamRound;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\DuskTestCase;

/**
 * PROTOTYPE (#254): throwaway. Screenshots each Season History variant at desktop and phone width.
 */
class SeasonHistoryPrototypeScreenshotTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    public function test_screenshot_variants(): void
    {
        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();
            $teamRound = TeamRound::where('name', '3x13 Kamps - Valid')->first();
            $base = '/app/c-'.$clubhouse->id.'/team-fight/'.$teamRound->id.'/edit';

            $browser->visit(new LoginPage)->loginSPA('testing@gmail.com', 'Test1234');

            foreach (['desktop' => [1400, 1000], 'phone' => [390, 844]] as $size => [$w, $h]) {
                $browser->resize($w, $h);
                foreach (["A", "B", "C", "D", "E"] as $variant) {
                    $browser->visit($base.'?variant='.$variant)
                        ->waitFor("[dusk='squad-0'] [dusk='edit-squad-member-button']", 20)
                        ->waitFor('@prototype-switcher')
                        ->pause(500);
                    if (in_array($variant, ["A", "D", "E"])) {
                        $browser->click("[dusk='squad-0'] [dusk='season-history-trigger']")->pause(800);
                    }
                    $browser->screenshot("season-history-{$size}-{$variant}");
                }
                $browser->visit($base.'?variant=C')
                    ->waitFor('.matrix tr.youth', 20)
                    ->script("document.querySelector('.matrix tr.youth').scrollIntoView({block: 'center'})");
                $youthName = $browser->script("return document.querySelector('.matrix tr.youth td').childNodes[0].textContent.trim()")[0];
                $browser->pause(300)->screenshot("season-history-{$size}-C-youth");
                $browser->visit($base.'?variant=E')
                    ->waitFor("[dusk='squad-0'] [dusk='edit-squad-member-button']", 20)
                    ->script("[...document.querySelectorAll('p.handle')].find(p => p.textContent.includes(".json_encode($youthName)."))?.click()");
                $browser->pause(800)->screenshot("season-history-{$size}-E-youth");
                $browser->script("document.querySelector(\"[dusk='graph-category-HD'] input\")?.click()");
                $browser->pause(500)->screenshot("season-history-{$size}-E-youth-HD");
                $browser->visit($base.'?variant=B&history-empty=1')
                    ->waitFor("[dusk='squad-0'] [dusk='edit-squad-member-button']", 20)
                    ->pause(500)
                    ->screenshot("season-history-{$size}-B-empty");
            }
        });
    }
}
