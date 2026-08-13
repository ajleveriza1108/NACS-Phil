<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\GalleryItem;
use App\Models\Inquiry;
use App\Models\SchoolEvent;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'announcements' => Announcement::count(),
                'events' => SchoolEvent::count(),
                'gallery' => GalleryItem::count(),
                'new_inquiries' => Inquiry::where('status', 'new')->count(),
            ],
            'recentInquiries' => Inquiry::latest()->limit(6)->get(),
            'upcomingEvents' => SchoolEvent::where('ends_at', '>=', now())->orderBy('starts_at')->limit(5)->get(),
        ]);
    }
}
