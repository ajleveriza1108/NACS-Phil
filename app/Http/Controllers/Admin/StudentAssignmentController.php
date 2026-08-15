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
            ]
        );

        StudentAudit::record(
            $request->user(),
            $student,
            'assignment.saved',
            StudentTeacherAssignment::class,
            $assignment,
            ['teacher_id','subject','is_adviser','can_manage_profile','can_manage_grades','can_manage_attendance'],
            'Teacher assignment saved.'
        );

        return back()->with('success', 'Teacher assignment saved.');
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
