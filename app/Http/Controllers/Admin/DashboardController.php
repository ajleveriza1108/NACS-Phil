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
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = request()->user();
        $counts = [];

        $recentInquiries = new Collection();
        $recentApplications = new Collection();
        $upcomingEvents = new Collection();
        $upcomingAcademicDates = new Collection();

        if ($user?->hasStaffPermission('students.manage')) {
            $counts['students'] = Student::count();
        }

        if ($user?->hasStaffPermission('news.manage')) {
            $counts['announcements'] = Announcement::count();
        }

        if ($user?->hasStaffPermission('events.manage')) {
            $counts['events'] = SchoolEvent::count();

            $upcomingEvents = SchoolEvent::query()
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();
        }

        if ($user?->hasStaffPermission('media.manage')) {
            $counts['gallery'] = GalleryItem::count();
        }

        if ($user?->hasStaffPermission('governance.manage')) {
            $counts['pending_content_reviews'] =
                Announcement::where('workflow_status', 'pending_review')->count()
                + SchoolEvent::where('workflow_status', 'pending_review')->count()
                + GalleryItem::where('workflow_status', 'pending_review')->count();
        }

        if ($user?->hasStaffPermission('admissions.manage')) {
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

            $recentInquiries = Inquiry::query()
                ->with('assignedTo')
                ->latest()
                ->limit(5)
                ->get();

            $recentApplications = AdmissionApplication::query()
                ->latest('submitted_at')
                ->limit(5)
                ->get();
        }

        if ($user?->hasStaffPermission('faculty.manage')) {
            $counts['faculty_published'] = FacultyProfile::where('is_published', true)->count();
        }

        if ($user?->hasStaffPermission('documents.manage')) {
            $counts['public_documents'] = SchoolDocument::query()
                ->where('audience', 'public')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->count();
        }

        if ($user?->hasStaffPermission('calendar.manage')) {
            $upcomingAcademicDates = AcademicCalendarEntry::query()
                ->where('is_published', true)
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();
        }

        if ($user?->hasStaffPermission('staff.manage')) {
            $counts['staff'] = User::where('is_admin', true)->count();
            $counts['super_admins'] = User::query()
                ->where('is_admin', true)
                ->where(function ($query): void {
                    $query->where('role', 'super_admin')->orWhereNull('role');
                })
                ->count();
            $counts['staff_without_2fa'] = User::query()
                ->where('is_admin', true)
                ->where('is_active', true)
                ->whereNull('two_factor_enabled_at')
                ->count();
        }

        return view('admin.dashboard', [
            'counts' => $counts,
            'recentInquiries' => $recentInquiries,
            'recentApplications' => $recentApplications,
            'upcomingEvents' => $upcomingEvents,
            'upcomingAcademicDates' => $upcomingAcademicDates,
            'showPriorityInbox' => $user?->hasAnyStaffPermission([
                'governance.manage',
                'admissions.manage',
            ]) ?? false,
        ]);
    }
}
