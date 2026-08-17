<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\AcademicRecordBuilder;
use App\Support\StudentAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicRecordController extends Controller
{
    public function reportCard(Request $request, Student $student): View
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $student->load([
            'grades.teacher:id,name',
            'attendances',
            'assignments' => fn ($query) => $query
                ->where('status', 'active')
                ->with('teacher:id,name,email'),
        ]);

        return view('academic.report-card', [
            'student' => $student,
            'record' => AcademicRecordBuilder::reportCard($student),
            'generatedBy' => $request->user(),
        ]);
    }

    public function transcript(Request $request, Student $student): View
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $official = $request->boolean('official');

        if ($official) {
            abort_unless(StudentAccess::isLeadership($request->user()), 403);
        }

        $student->load([
            'grades.teacher:id,name',
            'assignments' => fn ($query) => $query
                ->where('status', 'active')
                ->with('teacher:id,name,email'),
        ]);

        return view('academic.transcript', [
            'student' => $student,
            'years' => AcademicRecordBuilder::transcript($student),
            'generatedBy' => $request->user(),
            'official' => $official,
        ]);
    }
}
