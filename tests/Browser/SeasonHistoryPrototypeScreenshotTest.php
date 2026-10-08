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
                foreach (['A', 'B', 'C', 'D'] as $variant) {
                    $browser->visit($base.'?variant='.$variant)
                        ->waitFor("[dusk='squad-0'] [dusk='edit-squad-member-button']", 20)
                        ->waitFor('@prototype-switcher')
                        ->pause(500);
                    if ($variant !== 'D') {
                        $browser->click("[dusk='squad-0'] [dusk='season-history-trigger']")->pause(800);
                    }
                    $browser->screenshot("season-history-{$size}-{$variant}");
                }
                $browser->visit($base.'?variant=D&history-empty=1')
                    ->waitFor("[dusk='squad-0'] [dusk='edit-squad-member-button']", 20)
                    ->pause(500)
                    ->screenshot("season-history-{$size}-D-empty");
            }
        });
    }
}
