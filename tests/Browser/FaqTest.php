<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\FaqPage;
use Tests\Browser\Pages\LoginPage;
use Tests\DuskTestCase;

class FaqTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    public function test_user_can_read_faq(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new LoginPage())
                ->loginSPA('daniel@gmail.com', 'Test1234')
                ->visit(new FaqPage())
                ->on(new FaqPage())
                ->assertSee('FAQ')
                ->assertSee('Der vises en valideringsfejl selv om holdet er korrekt?')
                ->assertSee('Der mangler en spiller i min klub?')
                ->assertSee('Jeg har et forslag til forbedringer eller en ny feature?')
                ->assertSee('Der opstår en fejl, så jeg ikke kan komme videre?')
                ->assertSee('Hvad gør jeg, hvis holdene skal sættes efter forskellige ranglister?')
                ->assertSee('Hvilken rangliste skal jeg bruge?')
                ->assertSee('Spørgsmål til reglementet for DH-turneringen?')
                ->assertSee('Hvor ofte synkroniserer nembadminton.dk med badmintonplayer.dk?')
                ->assertSee('Hvordan virker validering af holdopstillingen?')
                ->assertSee('Er der taget højde for U15/U17/U19 spillere?')
                ->screenshot('faq-page-complete')
            ;
        });
    }
}
