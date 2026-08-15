<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentAttendanceController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageAttendance($request->user(), $student), 403);

        $data = $request->validate([
            'attendance_date' => ['required','date'],
            'status' => ['required', Rule::in(['present','absent','late','excused'])],
            'remarks' => ['nullable','string','max:1000'],
        ]);

        $attendance = StudentAttendance::updateOrCreate(
            [
                'student_id' => $student->id,
                'attendance_date' => $data['attendance_date'],
            ],
            [
                'recorded_by' => $request->user()->id,
                'status' => $data['status'],
                'remarks' => $data['remarks'] ?? null,
            ]
        );

        StudentAudit::record(
            $request->user(),
            $student,
            'attendance.saved',
            StudentAttendance::class,
            $attendance,
            ['attendance_date','status','remarks'],
            'Attendance record saved.'
        );

        return back()->with('success', 'Attendance saved.');
    }
}
