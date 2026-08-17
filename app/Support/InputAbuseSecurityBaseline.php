<?php

namespace App\Support;

final class InputAbuseSecurityBaseline
{
    public function summary(): array
    {
        $web = $this->read(base_path('routes/web.php'));
        $portal = $this->read(base_path('routes/student_portal.php'));
        $photo = $this->read(app_path('Http/Controllers/StudentProfilePhotoController.php'));
        $documents = $this->read(app_path('Http/Controllers/Admin/StudentDocumentController.php'));
        $finance = $this->read(app_path('Http/Controllers/Admin/StudentFinanceController.php'));
        $provider = $this->read(app_path('Providers/AppServiceProvider.php'));

        $checks = [

            $this->check(

                'route_scoped_sensitive_write_limiter',

                str_contains($provider, "RateLimiter::for('nacs-sensitive-write'")

                    && str_contains($provider, "->by('nacs-sensitive-write|'"),

                'Sensitive authenticated writes use route-scoped limiter buckets instead of one shared user bucket.'

            ),
            $this->check(
                'public_bot_and_rate_controls',
                str_contains($web, "turnstile:inquiry")
                    && str_contains($web, "turnstile:admissions_apply")
                    && str_contains($web, "turnstile:admissions_track"),
                'Public write surfaces retain Turnstile and rate controls.'
            ),
            $this->check(
                'student_grade_write_throttles',
                str_contains($portal, "throttle:nacs-sensitive-write')->name('students.grades.store')")
                    && str_contains($portal, "throttle:nacs-sensitive-write')->name('students.grades.destroy')"),
                'Grade creation and deletion have explicit abuse throttles.'
            ),
            $this->check(
                'student_sensitive_write_throttles',
                str_contains($portal, "throttle:nacs-sensitive-write')->name('students.attendance.store')")
                    && str_contains($portal, "throttle:nacs-sensitive-write')->name('students.finance.store')")
                    && str_contains($portal, "throttle:nacs-sensitive-write')->name('students.documents.store')"),
                'Attendance, finance, and confidential document writes have explicit throttles.'
            ),
            $this->check(
                'relationship_admin_write_throttles',
                str_contains($portal, "throttle:nacs-sensitive-write')->name('students.assignments.store')")
                    && str_contains($portal, "throttle:nacs-sensitive-write')->name('students.guardians.store')"),
                'Assignment and guardian relationship writes have explicit throttles.'
            ),
            $this->check(
                'admissions_document_delete_throttle',
                str_contains($web, "throttle:nacs-sensitive-write')->name('admissions.documents.destroy')"),
                'Family admissions document deletion is rate limited.'
            ),
            $this->check(
                'staff_security_action_throttle',
                str_contains($web, "throttle:nacs-sensitive-write')->name('staff.reset-two-factor')"),
                'Staff 2FA reset is rate limited.'
            ),
            $this->check(
                'content_upload_throttles',
                str_contains($web, "throttle:nacs-sensitive-write')->name('media.store')")
                    && str_contains($web, "throttle:nacs-sensitive-write')->name('branding.store')"),
                'Administrative media and branding uploads have explicit rate limits.'
            ),
            $this->check(
                'student_photo_allowlist',
                str_contains($photo, "'mimes:jpg,jpeg,png,webp'")
                    && str_contains($photo, "'dimensions:min_width='.\$minWidth"),
                'Student photo upload keeps an image/MIME/dimension allowlist.'
            ),
            $this->check(
                'confidential_document_external_only',
                str_contains($documents, "allow_local_fallback') === false")
                    && str_contains($documents, "Rule::in(['google_drive','google_cloud_storage','other_external'])"),
                'Confidential student documents remain external-reference only.'
            ),
            $this->check(
                'finance_numeric_validation',
                str_contains($finance, "'amount' => ['required','numeric','min:0.01']"),
                'Finance writes keep explicit numeric validation.'
            ),
        ];

        $failures = array_values(array_filter($checks, static fn (array $check): bool => ! $check['passed']));

        return [
            'ready' => $failures === [],
            'required_failures' => count($failures),
            'checks' => $checks,
        ];
    }

    private function read(string $path): string
    {
        return is_file($path) ? (string) file_get_contents($path) : '';
    }

    private function check(string $key, bool $passed, string $detail): array
    {
        return compact('key', 'passed', 'detail');
    }
}
