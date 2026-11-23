<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $services = Service::where('approved', 1)
            ->select('id', 'updated_at')
            ->get();

        $baseUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $entries = [
            ['path' => '/', 'lastmod' => now(), 'changefreq' => 'daily', 'priority' => '1.00'],
            ['path' => '/services', 'lastmod' => now(), 'changefreq' => 'weekly', 'priority' => '0.90'],
            ['path' => '/booking', 'lastmod' => now(), 'changefreq' => 'weekly', 'priority' => '0.80'],
            ['path' => '/about', 'lastmod' => now(), 'changefreq' => 'monthly', 'priority' => '0.70'],
            ['path' => '/gallery', 'lastmod' => now(), 'changefreq' => 'weekly', 'priority' => '0.70'],
            ['path' => '/contact', 'lastmod' => now(), 'changefreq' => 'monthly', 'priority' => '0.70'],
            ['path' => '/faq', 'lastmod' => now(), 'changefreq' => 'monthly', 'priority' => '0.70'],
            ['path' => '/team', 'lastmod' => now(), 'changefreq' => 'monthly', 'priority' => '0.60'],
        ];
        foreach ($services as $service) {
            $entries[] = [
                'path' => '/services/'.$service->id,
                'lastmod' => $service->updated_at ?? now(),
                'changefreq' => 'weekly',
                'priority' => '0.80',
            ];
        }

        $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($entries as $entry) {
            $loc = htmlspecialchars($baseUrl.$entry['path'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $lastmod = htmlspecialchars($entry['lastmod']->toAtomString(), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $content .= '<url><loc>'.$loc.'</loc><lastmod>'.$lastmod.'</lastmod><changefreq>'.$entry['changefreq'].'</changefreq><priority>'.$entry['priority'].'</priority></url>';
        }
        $content .= '</urlset>';

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
