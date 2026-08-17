<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentTeacherAssignment;
use App\Models\User;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StudentAssignmentController extends Controller
{
    public function requestExisting(Request $request): RedirectResponse
    {
        $teacher = $request->user();
        abort_unless(StudentAccess::canRequestExistingStudent($teacher), 403);

        $data = $request->validate([
            'student_number' => ['required', 'string', 'max:64'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'subject' => ['required', 'string', 'max:100'],
        ]);

        $student = Student::query()
            ->where('student_number', $data['student_number'])
            ->whereDate('date_of_birth', $data['date_of_birth'])
            ->first();

        if (! $student) {
            return back()->withErrors([
                'existing_student' => 'The student number and date of birth could not be verified. No student information was disclosed.',
            ])->withInput();
        }

        $existing = $student->assignments()
            ->where('teacher_id', $teacher->id)
            ->where('school_year', $student->school_year)
            ->whereRaw('lower(subject) = ?', [strtolower(trim($data['subject']))])
            ->first();

        if ($existing?->status === 'active') {
            return redirect()->route('admin.students.index')
                ->with('success', 'You are already assigned to this student for that subject.');
        }

        $assignment = StudentTeacherAssignment::updateOrCreate(
            [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'school_year' => $student->school_year,
                'subject' => trim($data['subject']),
            ],
            [
                'is_adviser' => false,
                'can_manage_profile' => false,
                'can_manage_grades' => true,
                'can_manage_attendance' => true,
                'status' => 'pending',
                'requested_by' => $teacher->id,
                'approved_by' => null,
                'approved_at' => null,
            ]
        );

        StudentAudit::record(
            $teacher,
            $student,
            'assignment.requested',
            StudentTeacherAssignment::class,
            $assignment,
            ['teacher_id', 'subject', 'status'],
            'Teacher requested access to an existing student record.'
        );

        return redirect()->route('admin.students.index')
            ->with('success', 'Existing student verified. Your teacher assignment is pending Principal / Super Administrator approval.');
    }

    public function store(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageAssignments($request->user()), 403);

        $data = $request->validate([
            'teacher_id' => ['required','integer','exists:users,id'],
            'subject' => ['nullable','string','max:100'],
            'is_adviser' => ['nullable','boolean'],
            'can_manage_profile' => ['nullable','boolean'],
            'can_manage_grades' => ['nullable','boolean'],
            'can_manage_attendance' => ['nullable','boolean'],
        ]);

        $teacher = User::query()->findOrFail($data['teacher_id']);
        abort_unless($teacher->is_admin === true && $teacher->isTeacher() && $teacher->is_active !== false, 422);

        $assignment = StudentTeacherAssignment::updateOrCreate(
            [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'school_year' => $student->school_year,
                'subject' => $data['subject'] ?: null,
            ],
            [
                'is_adviser' => $request->boolean('is_adviser'),
                'can_manage_profile' => $request->boolean('can_manage_profile'),
                'can_manage_grades' => $request->boolean('can_manage_grades'),
                'can_manage_attendance' => $request->boolean('can_manage_attendance'),
                'status' => 'active',
                'requested_by' => $request->user()->id,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]
        );

        StudentAudit::record(
            $request->user(),
            $student,
            'assignment.saved',
            StudentTeacherAssignment::class,
            $assignment,
            ['teacher_id','subject','is_adviser','can_manage_profile','can_manage_grades','can_manage_attendance','status'],
            'Teacher assignment saved.'
        );

        return back()->with('success', 'Teacher assignment saved.');
    }

    public function approve(
        Request $request,
        Student $student,
        StudentTeacherAssignment $assignment
    ): RedirectResponse {
        abort_unless(StudentAccess::canManageAssignments($request->user()), 403);
        abort_unless($assignment->student_id === $student->id, 404);

        $assignment->forceFill([
            'status' => 'active',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ])->save();

        StudentAudit::record(
            $request->user(),
            $student,
            'assignment.approved',
            StudentTeacherAssignment::class,
            $assignment,
            ['status','approved_by','approved_at'],
            'Teacher assignment request approved.'
        );

        return back()->with('success', 'Teacher assignment approved.');
    }

    public function reject(
        Request $request,
        Student $student,
        StudentTeacherAssignment $assignment
    ): RedirectResponse {
        abort_unless(StudentAccess::canManageAssignments($request->user()), 403);
        abort_unless($assignment->student_id === $student->id, 404);

        $assignment->forceFill([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ])->save();

        StudentAudit::record(
            $request->user(),
            $student,
            'assignment.rejected',
            StudentTeacherAssignment::class,
            $assignment,
            ['status','approved_by','approved_at'],
            'Teacher assignment request rejected.'
        );

        return back()->with('success', 'Teacher assignment request rejected.');
    }

    public function destroy(
        Request $request,
        Student $student,
        StudentTeacherAssignment $assignment
    ): RedirectResponse {
        abort_unless(StudentAccess::canManageAssignments($request->user()), 403);
        abort_unless($assignment->student_id === $student->id, 404);

        $assignmentId = $assignment->id;
        $assignment->delete();

        StudentAudit::record(
            $request->user(),
            $student,
            'assignment.deleted',
            StudentTeacherAssignment::class,
            $assignmentId,
            ['deleted'],
            'Teacher assignment removed.'
        );

        return back()->with('success', 'Teacher assignment removed.');
    }
}
