<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentGradeController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required','string','max:100'],
            'term' => ['required', Rule::in(['Q1','Q2','Q3','Q4','Final'])],
            'category' => ['required', Rule::in(['written_work','performance_task','quiz','exam','project','final_grade','other'])],
            'assessment_name' => ['required','string','max:160'],
            'score' => ['nullable','numeric','min:0'],
            'max_score' => ['nullable','numeric','min:0.01'],
            'grade_percentage' => ['nullable','numeric','min:0','max:100'],
            'remarks' => ['nullable','string','max:1000'],
            'assessment_date' => ['nullable','date'],
        ]);

        abort_unless(
            StudentAccess::canManageGrades($request->user(), $student, $data['subject']),
            403
        );

        if (
            empty($data['grade_percentage'])
            && isset($data['score'], $data['max_score'])
            && (float) $data['max_score'] > 0
        ) {
            $data['grade_percentage'] = round(((float) $data['score'] / (float) $data['max_score']) * 100, 2);
        }

        $grade = $student->grades()->create([
            ...$data,
            'teacher_id' => $request->user()->id,
            'school_year' => $student->school_year,
        ]);

        StudentAudit::record(
            $request->user(),
            $student,
            'grade.created',
            StudentGrade::class,
            $grade,
            array_keys($data),
            'Academic grade or assessment result recorded.'
        );

        return back()->with('success', 'Grade or assessment result recorded.');
    }

    public function destroy(Request $request, Student $student, StudentGrade $grade): RedirectResponse
    {
        abort_unless($grade->student_id === $student->id, 404);
        abort_unless(
            StudentAccess::canManageGrades($request->user(), $student, $grade->subject),
            403
        );

        $gradeId = $grade->id;
        $grade->delete();

        StudentAudit::record(
            $request->user(),
            $student,
            'grade.deleted',
            StudentGrade::class,
            $gradeId,
            ['deleted'],
            'Academic grade or assessment record removed.'
        );

        return back()->with('success', 'Grade or assessment result removed.');
    }
}
