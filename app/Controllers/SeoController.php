<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BookRepository;
use App\Repositories\GenreRepository;

/**
 * Fisierele pentru motoarele de cautare:
 *  - robots.txt: ce pagini pot fi indexate si unde e harta site-ului
 *  - sitemap.xml: lista tuturor paginilor publice (generata din baza de date)
 */
class SeoController extends Controller
{
    /** Zonele private nu au ce cauta in rezultatele cautarii. */
    private const PRIVATE_PATHS = ['/cont', '/admin', '/autor', '/comanda', '/cumpara', '/descarca',
                                   '/autentificare', '/inregistrare', '/captcha'];

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $lines = ['User-agent: *'];
        foreach (self::PRIVATE_PATHS as $path) {
            $lines[] = 'Disallow: ' . url($path);
        }
        $lines[] = '';
        $lines[] = 'Sitemap: ' . absolute_url('/sitemap.xml');

        echo implode("\n", $lines) . "\n";
    }

    public function sitemap(): void
    {
        $urls = [
            ['loc' => absolute_url('/'),           'changefreq' => 'daily',  'priority' => '1.0'],
            ['loc' => absolute_url('/carti'),      'changefreq' => 'daily',  'priority' => '0.9'],
            ['loc' => absolute_url('/blind-date'), 'changefreq' => 'weekly', 'priority' => '0.8'],
        ];

        foreach ((new GenreRepository())->findAll() as $genre) {
            $urls[] = ['loc' => absolute_url('/carti?gen=' . $genre->id), 'changefreq' => 'weekly', 'priority' => '0.6'];
        }

        foreach ((new BookRepository())->latestApproved(1000) as $book) {
            $urls[] = [
                'loc'        => absolute_url('/carte/' . $book->slug),
                'lastmod'    => date('Y-m-d', strtotime($book->createdAt)),
                'changefreq' => 'monthly',
                'priority'   => '0.7',
            ];
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            foreach ($u as $tag => $value) {
                echo "    <{$tag}>" . htmlspecialchars($value, ENT_XML1) . "</{$tag}>\n";
            }
            echo "  </url>\n";
        }
        echo "</urlset>\n";
    }
}
