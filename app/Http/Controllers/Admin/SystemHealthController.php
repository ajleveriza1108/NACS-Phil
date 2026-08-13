<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\Announcement;
use App\Models\FacultyProfile;
use App\Models\GalleryItem;
use App\Models\Inquiry;
use App\Models\SchoolDocument;
use App\Models\SchoolEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Throwable;

class SystemHealthController extends Controller
{
    public function __invoke(): View
    {
        $databaseOk = true;

        try {
            DB::select('select 1');
        } catch (Throwable) {
            $databaseOk = false;
        }

        $privateAdmissions = storage_path('app/private/admissions');
        $privateDocuments = storage_path('app/private/documents');

        foreach ([$privateAdmissions, $privateDocuments] as $path) {
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0750, true);
            }
        }

        $twoFactorMissing = User::query()
            ->where('is_admin', true)
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereIn('role', ['super_admin','principal'])->orWhereNull('role');
            })
            ->whereNull('two_factor_enabled_at')
            ->count();

        $backupRoot = base_path('.nacs-backups');
        $latestBackup = null;

        if (File::isDirectory($backupRoot)) {
            $directories = collect(File::directories($backupRoot))
                ->sortByDesc(fn (string $path) => File::lastModified($path));

            $latestBackup = $directories->first();
        }

        return view('admin.system-health.index', [
            'databaseOk' => $databaseOk,
            'privateAdmissionsOk' => is_writable($privateAdmissions),
            'privateDocumentsOk' => is_writable($privateDocuments),
            'debugEnabled' => (bool) config('app.debug'),
            'environment' => app()->environment(),
            'twoFactorMissing' => $twoFactorMissing,
            'latestBackup' => $latestBackup ? basename($latestBackup) : null,
            'counts' => [
                'announcements' => Announcement::count(),
                'events' => SchoolEvent::count(),
                'gallery' => GalleryItem::count(),
                'faculty' => FacultyProfile::count(),
                'documents' => SchoolDocument::count(),
                'inquiries_open' => Inquiry::whereNotIn('status', ['resolved','closed'])->count(),
                'applications_active' => AdmissionApplication::whereNotIn('status', ['declined','withdrawn','enrolled'])->count(),
                'pending_reviews' => Announcement::where('workflow_status','pending_review')->count()
                    + SchoolEvent::where('workflow_status','pending_review')->count()
                    + GalleryItem::where('workflow_status','pending_review')->count(),
            ],
        ]);
    }
}
