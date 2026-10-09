<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Lists every public Help route for search engines (ADR 0004), derived from
 * the Markdown files in resources/help so new content needs no manual upkeep.
 */
class HelpSitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim((string) config('app.url'), '/').'/app/help';

        $urls = [
            ['loc' => $base],
            ['loc' => $base.'/guides'],
            ['loc' => $base.'/news'],
        ];
        foreach ($this->slugs('guides') as $slug) {
            $urls[] = ['loc' => $base.'/guides/'.$slug];
        }
        foreach ($this->slugs('news') as $slug) {
            // The Help validator requires news filenames to start with their published date.
            $urls[] = ['loc' => $base.'/news/'.$slug, 'lastmod' => substr($slug, 0, 10)];
        }
        foreach ($this->slugs('pages') as $slug) {
            $urls[] = ['loc' => $base.'/'.$slug];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * @return list<string>
     */
    private function slugs(string $directory): array
    {
        return array_map(
            fn (string $path) => basename($path, '.md'),
            glob(resource_path("help/{$directory}/*.md")) ?: []
        );
    }
}
