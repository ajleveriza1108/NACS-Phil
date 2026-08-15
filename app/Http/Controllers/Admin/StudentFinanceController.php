<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentFinancialEntry;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentFinanceController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageFinance($request->user()), 403);

        $data = $request->validate([
            'entry_type' => ['required', Rule::in(['charge','payment','credit','adjustment'])],
            'description' => ['required','string','max:180'],
            'amount' => ['required','numeric','min:0.01'],
            'reference_number' => ['nullable','string','max:100'],
            'entry_date' => ['required','date'],
            'due_date' => ['nullable','date'],
        ]);

        $entry = $student->financialEntries()->create([
            ...$data,
            'recorded_by' => $request->user()->id,
            'school_year' => $student->school_year,
            'classification' => 'highly_confidential',
        ]);

        StudentAudit::record(
            $request->user(),
            $student,
            'finance.created',
            StudentFinancialEntry::class,
            $entry,
            array_keys($data),
            'Financial ledger entry recorded.'
        );

        return back()->with('success', 'Financial entry recorded.');
    }
}
