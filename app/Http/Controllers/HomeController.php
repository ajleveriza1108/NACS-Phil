<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\GalleryItem;
use App\Models\SchoolEvent;
use App\Models\SiteContent;
use App\Support\HomeContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'homeContent' => SiteContent::valuesFor('home', HomeContent::defaults()),
            'featuredAnnouncement' => Announcement::published()
                ->where('is_featured', true)
                ->orderByDesc('is_pinned')
                ->orderBy('sort_order')
                ->latest('published_at')
                ->first(),
            'announcements' => Announcement::published()
                ->orderByDesc('is_pinned')
                ->orderBy('sort_order')
                ->latest('published_at')
                ->limit(3)
                ->get(),
            'events' => SchoolEvent::published()
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),
            'galleryItems' => GalleryItem::published()
                ->orderBy('sort_order')
                ->latest('taken_at')
                ->limit(6)
                ->get(),
        ]);
    }
}
