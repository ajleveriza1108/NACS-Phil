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
use Illuminate\Support\Facades\Schema;
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
        $missingTables = [];

        $tableReady = static function (string $modelClass) use (&$missingTables): bool {
            $model = new $modelClass();
            $table = $model->getTable();

            if (Schema::hasTable($table)) {
                return true;
            }

            $missingTables[] = $table;

            return false;
        };

        if ($user?->hasStaffPermission('students.manage') && $tableReady(Student::class)) {
            $counts['students'] = Student::count();
        }

        if ($user?->hasStaffPermission('news.manage') && $tableReady(Announcement::class)) {
            $counts['announcements'] = Announcement::count();
        }

        if ($user?->hasStaffPermission('events.manage') && $tableReady(SchoolEvent::class)) {
            $counts['events'] = SchoolEvent::count();

            $upcomingEvents = SchoolEvent::query()
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();
        }

        if ($user?->hasStaffPermission('media.manage') && $tableReady(GalleryItem::class)) {
            $counts['gallery'] = GalleryItem::count();
        }

        if ($user?->hasStaffPermission('governance.manage')) {
            $pendingReviews = 0;

            if ($tableReady(Announcement::class)) {
                $pendingReviews += Announcement::where('workflow_status', 'pending_review')->count();
            }

            if ($tableReady(SchoolEvent::class)) {
                $pendingReviews += SchoolEvent::where('workflow_status', 'pending_review')->count();
            }

            if ($tableReady(GalleryItem::class)) {
                $pendingReviews += GalleryItem::where('workflow_status', 'pending_review')->count();
            }

            $counts['pending_content_reviews'] = $pendingReviews;
        }

        if ($user?->hasStaffPermission('admissions.manage')) {
            if ($tableReady(Inquiry::class)) {
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

                $recentInquiries = Inquiry::query()
                    ->with('assignedTo')
                    ->latest()
                    ->limit(5)
                    ->get();
            }

            if ($tableReady(AdmissionApplication::class)) {
                $counts['admission_applications'] = AdmissionApplication::count();
                $counts['admissions_waiting_review'] = AdmissionApplication::whereIn(
                    'status',
                    ['submitted', 'under_review']
                )->count();
                $counts['admissions_awaiting_documents'] = AdmissionApplication::where(
                    'status',
                    'awaiting_documents'
                )->count();

                $recentApplications = AdmissionApplication::query()
                    ->latest('submitted_at')
                    ->limit(5)
                    ->get();
            }
        }

        if ($user?->hasStaffPermission('faculty.manage') && $tableReady(FacultyProfile::class)) {
            $counts['faculty_published'] = FacultyProfile::where('is_published', true)->count();
        }

        if ($user?->hasStaffPermission('documents.manage') && $tableReady(SchoolDocument::class)) {
            $counts['public_documents'] = SchoolDocument::query()
                ->where('audience', 'public')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->count();
        }

        if ($user?->hasStaffPermission('calendar.manage') && $tableReady(AcademicCalendarEntry::class)) {
            $upcomingAcademicDates = AcademicCalendarEntry::query()
                ->where('is_published', true)
                ->where('ends_at', '>=', now()->startOfDay())
                ->orderBy('starts_at')
                ->limit(5)
                ->get();
        }

        if ($user?->hasStaffPermission('staff.manage')) {
            // The users table is already proven available if this authenticated
            // request reached the dashboard, so these account-readiness metrics
            // do not need an additional schema query.
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

        $missingTables = array_values(array_unique($missingTables));

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
            'databaseSetupIncomplete' => $missingTables !== [],
            'missingTableCount' => count($missingTables),
        ]);
    }
}
