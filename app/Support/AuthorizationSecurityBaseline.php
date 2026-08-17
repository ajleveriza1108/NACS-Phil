<?php

namespace App\Support;

final class AuthorizationSecurityBaseline
{
    public function summary(): array
    {
        $studentAccess = $this->read(app_path('Support/StudentAccess.php'));
        $grades = $this->read(app_path('Http/Controllers/Admin/StudentGradeController.php'));
        $finance = $this->read(app_path('Http/Controllers/Admin/StudentFinanceController.php'));
        $documents = $this->read(app_path('Http/Controllers/Admin/StudentDocumentController.php'));
        $academic = $this->read(app_path('Http/Controllers/AcademicRecordController.php'));
        $web = $this->read(base_path('routes/web.php'));

        $checks = [
            $this->check(
                'student_default_deny',
                str_contains($studentAccess, "return \$query->whereRaw('1 = 0');"),
                'Unknown student actors resolve to an empty/default-deny query.'
            ),
            $this->check(
                'teacher_relationship_scope',
                str_contains($studentAccess, "->where('teacher_id', \$user->id)")
                    && str_contains($studentAccess, "->where('status', 'active')"),
                'Teacher student access remains relationship-scoped to active assignments.'
            ),
            $this->check(
                'grade_child_object_binding',
                str_contains($grades, 'abort_unless($grade->student_id === $student->id, 404);')
                    && str_contains($grades, 'StudentAccess::canManageGrades'),
                'Grade child objects are bound to the parent student and subject authorization.'
            ),
            $this->check(
                'finance_leadership_only',
                str_contains($finance, 'StudentAccess::canManageFinance($request->user())'),
                'Student finance mutation remains leadership-only.'
            ),
            $this->check(
                'student_documents_leadership_only',
                str_contains($documents, 'StudentAccess::canManageDocuments($request->user())'),
                'Confidential student document registration remains leadership-only.'
            ),
            $this->check(
                'academic_record_relationship_guard',
                substr_count($academic, 'StudentAccess::canViewStudent($request->user(), $student)') >= 2,
                'Report card and transcript access both use relationship-aware authorization.'
            ),
            $this->check(
                'staff_management_permission',
                str_contains($web, "staff_permission:staff.manage"),
                'Staff-account management remains behind its dedicated least-privilege permission.'
            ),
            $this->check(
                'admissions_object_boundaries',
                str_contains($web, "middleware('admission.access')")
                    && str_contains($web, "staff_permission:admissions.manage"),
                'Family admissions access and staff admissions management use separate protected boundaries.'
            ),
            $this->check(
                'future_api_default_off',
                config('nacs_security.future.mobile_api.enabled') === false,
                'Future API access remains disabled until it can reuse the same authorization model.'
            ),
        ];

        return $this->report($checks);
    }

    private function read(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function check(string $key, bool $passed, string $detail): array
    {
        return compact('key', 'passed', 'detail');
    }

    private function report(array $checks): array
    {
        $failures = array_values(array_filter($checks, static fn (array $check): bool => ! $check['passed']));

        return [
            'ready' => $failures === [],
            'required_failures' => count($failures),
            'checks' => $checks,
        ];
    }
}
