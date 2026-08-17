<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentProfilePhotoController extends Controller
{
    public function show(Request $request, Student $student): StreamedResponse
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $disk = (string) ($student->profile_photo_disk ?: config('student_portal.profile_photo.disk', 'local'));
        $path = (string) $student->profile_photo_path;

        abort_if($path === '' || ! Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response(
            $path,
            'student-photo',
            [
                'Content-Type' => $student->profile_photo_mime_type ?: 'image/jpeg',
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageProfile($request->user(), $student), 403);

        $maxKb = (int) config('student_portal.profile_photo.max_kb', 1024);
        $minWidth = (int) config('student_portal.profile_photo.min_width', 400);
        $minHeight = (int) config('student_portal.profile_photo.min_height', 400);

        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:'.$maxKb,
                'dimensions:min_width='.$minWidth.',min_height='.$minHeight,
            ],
        ]);

        $file = $request->file('profile_photo');
        $disk = (string) config('student_portal.profile_photo.disk', 'local');
        $directory = 'students/'.$student->public_id.'/profile';
        $name = 'profile-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(5)).'.'.$file->guessExtension();
        $newPath = $file->storeAs($directory, $name, $disk);

        $oldDisk = (string) $student->profile_photo_disk;
        $oldPath = (string) $student->profile_photo_path;

        $student->forceFill([
            'profile_photo_disk' => $disk,
            'profile_photo_path' => $newPath,
            'profile_photo_mime_type' => $file->getMimeType(),
            'profile_photo_size_bytes' => $file->getSize(),
        ])->save();

        if ($oldPath !== '' && $oldPath !== $newPath && $oldDisk !== '') {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        StudentAudit::record(
            $request->user(),
            $student,
            'profile_photo.updated',
            Student::class,
            $student,
            ['profile_photo_path', 'profile_photo_mime_type', 'profile_photo_size_bytes'],
            'Private student profile photo updated.'
        );

        return back()->with('success', 'Student profile photo updated in private storage.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageProfile($request->user(), $student), 403);

        $disk = (string) $student->profile_photo_disk;
        $path = (string) $student->profile_photo_path;

        if ($path !== '' && $disk !== '') {
            Storage::disk($disk)->delete($path);
        }

        $student->forceFill([
            'profile_photo_disk' => null,
            'profile_photo_path' => null,
            'profile_photo_mime_type' => null,
            'profile_photo_size_bytes' => null,
        ])->save();

        StudentAudit::record(
            $request->user(),
            $student,
            'profile_photo.removed',
            Student::class,
            $student,
            ['profile_photo_path'],
            'Private student profile photo removed.'
        );

        return back()->with('success', 'Student profile photo removed.');
    }
}
