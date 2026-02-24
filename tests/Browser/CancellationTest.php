<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\CancellationDashboardPage;
use Tests\Browser\Pages\CancellationLandingPage;
use Tests\Browser\Pages\CreateCancellationPage;
use Tests\Browser\Pages\LoginPage;
use Tests\Browser\Pages\PublicCancellationFinishPage;
use Tests\Browser\Pages\PublicCancellationPage;
use Tests\Browser\Pages\UpdateCancellationPage;
use Tests\DuskTestCase;

class CancellationTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    public function testCancellationFlow(): void
    {
        $this->browse(function (Browser $browser) {
            // 1. Login
            $browser->visit(new LoginPage())
                ->loginSPA('testing@gmail.com', 'Test1234');

            // 2. Go to cancellations (via sidebar or direct URL)
            // For now, direct URL to landing
            $browser->visit(new CancellationLandingPage())
                ->click('@get-started');

            // 3. Create cancellation collector
            $browser->on(new CreateCancellationPage())
                ->type('@clubs', 'BC37 Amager')
                ->waitForText('BC37 Amager')
                ->keys('@clubs', ['{enter}'])
                ->createCollector('notif@example.com');

            // 4. Verify on Dashboard
            $browser->on(new CancellationDashboardPage())
                ->assertSee('Afbudslink')
                ->assertSee('BC37 Amager');

            // Get sharing ID from the page (this might be tricky in Dusk without specific tags)
            // But I can see it in the URL of the public link if I can find it.
            // In CancellationCollector.vue, it shows the link.
            $sharingId = $browser->value('[dusk="public-link"]');
            // Extract UUID from href like ".../cancellation/UUID/public-cancellation"
            preg_match('/cancellation\/(.*)\/public-cancellation/', $sharingId, $matches);
            $uuid = $matches[1];

            // 5. Submit a public cancellation
            $browser->visit(new PublicCancellationPage($uuid))
                ->submitCancellation('Daniel Fly Nygaard', 'daniel@example.com', 'I cannot play today');

            // 6. Verify finish page
            $browser->on(new PublicCancellationFinishPage($uuid))
                ->assertSee('Tak for at registrere afbud');

            // 7. Go back to dashboard and see the cancellation
            $browser->visit(new CancellationDashboardPage())
                ->on(new CancellationDashboardPage())
                ->waitFor('@table')
                ->assertSee('Daniel Fly Nygaard')
                ->assertSee('I cannot play today');

            // 8. Update collector
            $browser->click('@edit')
                ->on(new UpdateCancellationPage())
                ->updateCollector('updated@example.com');

            $browser->on(new CancellationDashboardPage())
                ->assertSee('updated@example.com');

            // 9. Delete collector
            $browser->click('@delete')
                ->waitForText('Er du sikker?')
                ->click('.modal-card-foot .button.is-danger');

            $browser->on(new CancellationLandingPage());
        });
    }
}
