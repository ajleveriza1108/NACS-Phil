<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolEvent;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            route('home'),
            route('about'),
            route('programs'),
            route('admissions'),
            route('faculty.index'),
            route('documents.index'),
            route('calendar.index'),
            route('announcements.index'),
            route('events.index'),
            route('gallery.index'),
            route('media.index'),
            route('contact'),
            route('privacy'),
        ]);

        $urls = $urls
            ->merge(Announcement::published()->get()->map(fn ($item) => route('announcements.show', $item)))
            ->merge(SchoolEvent::published()->get()->map(fn ($item) => route('events.show', $item)))
            ->unique();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .$urls->map(fn (string $url) => '<url><loc>'.e($url).'</loc></url>')->implode('')
            .'</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /admissions/status/\nSitemap: ".url('/sitemap.xml')."\n";

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
