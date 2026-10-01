<?php

namespace Tests\Browser;

use App\Models\Clubhouse;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\TeamFightCreatePage;
use Tests\DuskTestCase;

class RankingVersionRepublishedTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected $seeder = 'TestingDataSeeder';

    public function test_the_newest_version_of_a_republished_month_is_hinted_and_auto_selected(): void
    {
        // Badminton Danmark re-published February 2026 (originally 2026-02-02) as 2026-02-01.
        DB::insert(<<<'SQL'
            insert into points (points, position, cll, clh, category, vintage, member_id, version, created_at, updated_at)
            select points, position, cll, clh, category, vintage, member_id, '2026-02-01', now(), now()
            from points where version = '2026-02-02'
        SQL);

        $this->browse(function (Browser $browser) {
            $clubhouse = Clubhouse::first();

            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234')
                ->visit(new TeamFightCreatePage($clubhouse->id))
                ->on(new TeamFightCreatePage($clubhouse->id));

            // February 15, 2026 falls inside both February versions' ranking window.
            $browser->on(new TeamFightCreatePage($clubhouse->id))
                ->selectDate(2, 2026, 15);

            $browser->waitFor('@ranking-select')
                ->waitUsing(5, 100, function () use ($browser) {
                    return (bool) $browser->script("return document.querySelector(\"[dusk='team-fight-ranking-select']\").value;")[0];
                });

            $this->assertSame('2026-02-01', $browser->value('@ranking-select'));

            $options = $browser->script(<<<'JS'
                return Array.from(document.querySelector("[dusk='team-fight-ranking-select']").options)
                    .map(option => option.text.replace(/\s+/g, ' ').trim());
            JS)[0];
            $this->assertContains('Februar 2026 (01.02 – nyeste) (Indstillet automatisk)', $options);
            $this->assertContains('Februar 2026 (02.02)', $options);
            $this->assertContains('Januar 2026', $options);
        });
    }
}
