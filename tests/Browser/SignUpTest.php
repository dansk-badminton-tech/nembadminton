<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SignUpTest extends DuskTestCase
{

    use DatabaseTruncation;

    protected string $seeder = 'TestingDataSeeder';

    /**
     * A Dusk test example.
     */
    public function testSignUp(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/app/sign-up')
                    ->type('@name-input', 'Daniel Fly Nygaard')
                    ->type('@email-input', 'daniel@gmail.com')
                    ->type('@password-input', 'Test1234')
                    ->type('@password-confirmation-input', 'Test1234')
                    ->type('@name-input', 'Daniel Fly Nygaard')
                    ->check('@term-checkbox')
                    ->screenshot('signup');
        });
    }
}
