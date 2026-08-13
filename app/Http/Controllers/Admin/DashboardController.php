<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendarEntry;
use App\Models\AdmissionApplication;
use App\Models\Announcement;
use App\Models\FacultyProfile;
use App\Models\GalleryItem;
use App\Models\Inquiry;
use App\Models\SchoolDocument;
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

        $recentInquiries = new Collection();
        $recentApplications = new Collection();
        $upcomingAcademicDates = new Collection();

        if ($canManageSettings) {
            $counts['new_inquiries'] = Inquiry::where('status', 'new')->count();
            $counts['due_followups'] = Inquiry::query()
                ->whereNotNull('follow_up_at')
                ->where('follow_up_at', '<=', now())
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();
            $counts['unassigned_inquiries'] = Inquiry::query()
                ->whereNull('assigned_to_user_id')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count();

            $counts['admission_applications'] = AdmissionApplication::count();
            $counts['admissions_waiting_review'] = AdmissionApplication::whereIn(
                'status',
                ['submitted', 'under_review']
            )->count();
            $counts['admissions_awaiting_documents'] = AdmissionApplication::where(
                'status',
                'awaiting_documents'
            )->count();

            $counts['faculty'] = FacultyProfile::count();
            $counts['faculty_published'] = FacultyProfile::where('is_published', true)->count();
            $counts['documents'] = SchoolDocument::count();
            $counts['public_documents'] = SchoolDocument::query()
                ->where('audience', 'public')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->count();

            $counts['pending_content_reviews'] =
                Announcement::where('workflow_status', 'pending_review')->count()
                + SchoolEvent::where('workflow_status', 'pending_review')->count()
                + GalleryItem::where('workflow_status', 'pending_review')->count();

            $recentInquiries = Inquiry::query()
                ->with('assignedTo')
                ->latest()
                ->limit(5)
                ->get();

            $recentApplications = AdmissionApplication::query()
                ->latest('submitted_at')
                ->limit(5)
                ->get();

            $upcomingAcademicDates = AcademicCalendarEntry::query()
                ->where('is_published', true)
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();
        }

        if ($canManageStaff) {
            $counts['staff'] = User::where('is_admin', true)->count();
            $counts['leadership_without_2fa'] = User::query()
                ->where('is_admin', true)
                ->where('is_active', true)
                ->whereIn('role', ['super_admin', 'principal'])
                ->whereNull('two_factor_enabled_at')
                ->count();
        }

        return view('admin.dashboard', [
            'counts' => $counts,
            'recentInquiries' => $recentInquiries,
            'recentApplications' => $recentApplications,
            'upcomingAcademicDates' => $upcomingAcademicDates,
            'upcomingEvents' => SchoolEvent::query()
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get(),
            'canManageSettings' => $canManageSettings,
            'canManageStaff' => $canManageStaff,
        ]);
    }
}
