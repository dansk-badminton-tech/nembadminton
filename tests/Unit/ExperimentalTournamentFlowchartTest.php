<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExperimentalTournamentFlowchartTest extends TestCase
{
    #[Test]
    public function it_serves_the_standalone_flowchart_without_authentication(): void
    {
        $this->assertGuest();

        $response = $this->get('/eksperimenter/holdturnering');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertViewIs('experiments.holdturnering')
            ->assertSee('Hele sæsonen. Ét flow.')
            ->assertSee('Eksperiment')
            ->assertSee('1. juli 2026')
            ->assertSee('id="chart"', false)
            ->assertDontSee('file://', false)
            ->assertDontSee('localhost', false)
            ->assertDontSee('@vite', false)
            ->assertDontSee('/build/assets/', false);

        $this->assertGuest();
    }

    #[Test]
    public function it_links_to_the_public_regulation_and_explains_the_limitations(): void
    {
        $this->get(route('experiments.holdturnering'))
            ->assertOk()
            ->assertSee('https://badminton.dk/wp-content/uploads/2026/07/Holdturneringsreglement-for-badminton-i-Danmark-DH-reglementet-2026-07-01-endeligt-med-bilag-3-1.pdf', false)
            ->assertSee('ikke en officiel regelvejledning')
            ->assertSee('Klubbegrænsninger, afbud og andre undtagelser er ikke udtegnet.');
    }
}
