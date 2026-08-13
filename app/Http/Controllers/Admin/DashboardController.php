<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\Announcement;
use App\Models\GalleryItem;
use App\Models\Inquiry;
use App\Models\SchoolEvent;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $canManageSettings = $user?->canManageSchoolSettings() ?? false;
        $canManageStaff = $user?->canManageStaff() ?? false;

        $counts = [
            'announcements' => Announcement::count(),
            'events' => SchoolEvent::count(),
            'gallery' => GalleryItem::count(),
        ];

        if ($canManageSettings) {
            $counts['new_inquiries'] = Inquiry::where('status', 'new')->count();
            $counts['admission_applications'] = AdmissionApplication::count();
            $counts['admissions_waiting_review'] = AdmissionApplication::whereIn('status', ['submitted','under_review'])->count();
        }

        if ($canManageStaff) {
            $counts['staff'] = User::where('is_admin', true)->count();
        }

        $recentInquiries = $canManageSettings
            ? Inquiry::latest()->limit(6)->get()
            : new Collection();

        return view('admin.dashboard', [
            'counts' => $counts,
            'recentInquiries' => $recentInquiries,
            'upcomingEvents' => SchoolEvent::where('ends_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
            'canManageSettings' => $canManageSettings,
            'canManageStaff' => $canManageStaff,
        ]);
    }
}