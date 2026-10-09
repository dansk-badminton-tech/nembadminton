<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use SimpleXMLElement;
use Tests\TestCase;

/**
 * ADR 0004: /sitemap.xml lists every public Help route, derived from resources/help/.
 */
class HelpSitemapTest extends TestCase
{
    private const NAMESPACE = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    #[Test]
    public function it_serves_an_xml_urlset_without_authentication(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringStartsWith('application/xml', $response->headers->get('Content-Type'));

        $xml = new SimpleXMLElement($response->getContent());
        $this->assertSame('urlset', $xml->getName());
        $this->assertContains(self::NAMESPACE, $xml->getNamespaces());
    }

    #[Test]
    public function it_lists_exactly_the_help_routes_for_the_markdown_files(): void
    {
        $base = rtrim(config('app.url'), '/').'/app/help';
        $expected = [$base, $base.'/guides', $base.'/news'];
        foreach ($this->slugs('guides') as $slug) {
            $expected[] = $base.'/guides/'.$slug;
        }
        foreach ($this->slugs('news') as $slug) {
            $expected[] = $base.'/news/'.$slug;
        }
        foreach ($this->slugs('pages') as $slug) {
            $expected[] = $base.'/'.$slug;
        }

        $actual = array_keys($this->entries());

        sort($expected);
        sort($actual);
        $this->assertSame($expected, $actual);
    }

    #[Test]
    public function only_release_announcements_carry_their_filename_date_as_lastmod(): void
    {
        $base = rtrim(config('app.url'), '/').'/app/help';
        $news = $this->slugs('news');
        $this->assertNotEmpty($news);

        foreach ($this->entries() as $loc => $entry) {
            $slug = substr($loc, strlen($base.'/news/'));
            if (str_starts_with($loc, $base.'/news/') && in_array($slug, $news, true)) {
                $this->assertSame(substr($slug, 0, 10), (string) $entry->lastmod, $loc);
            } else {
                $this->assertSame(0, $entry->lastmod->count(), $loc);
            }
            $this->assertSame(0, $entry->priority->count(), $loc);
            $this->assertSame(0, $entry->changefreq->count(), $loc);
        }
    }

    #[Test]
    public function robots_txt_points_to_the_sitemap(): void
    {
        $this->assertStringContainsString(
            'Sitemap: https://nembadminton.dk/sitemap.xml',
            file_get_contents(public_path('robots.txt'))
        );
    }

    /**
     * @return array<string, SimpleXMLElement>
     */
    private function entries(): array
    {
        $xml = new SimpleXMLElement($this->get('/sitemap.xml')->getContent());

        $entries = [];
        foreach ($xml->children(self::NAMESPACE)->url as $url) {
            $entries[(string) $url->loc] = $url;
        }

        return $entries;
    }

    /**
     * @return list<string>
     */
    private function slugs(string $directory): array
    {
        return array_map(
            fn (string $path) => basename($path, '.md'),
            glob(resource_path("help/{$directory}/*.md"))
        );
    }
}
