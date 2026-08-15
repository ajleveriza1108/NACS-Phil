<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Support\StudentAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $students = StudentAccess::visibleStudentQuery($request->user())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('portal.dashboard', compact('students'));
    }

    public function show(Request $request, Student $student): View
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $student->load([
            'grades' => fn ($query) => $query->latest('assessment_date')->latest('id'),
            'attendances' => fn ($query) => $query->latest('attendance_date'),
        ]);

        $canViewFinance = StudentAccess::canViewFinance($request->user(), $student);

        if ($canViewFinance) {
            $student->load([
                'financialEntries' => fn ($query) => $query->latest('entry_date')->latest('id'),
            ]);
        }

        return view('portal.student', compact('student', 'canViewFinance'));
    }
}
