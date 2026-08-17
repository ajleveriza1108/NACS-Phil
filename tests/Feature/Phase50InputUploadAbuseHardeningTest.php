<?php

namespace Tests\Feature;

use App\Support\InputAbuseSecurityBaseline;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase50InputUploadAbuseHardeningTest extends TestCase
{
    public function test_input_upload_and_abuse_source_baseline_passes(): void
    {
        $report = app(InputAbuseSecurityBaseline::class)->summary();

        $this->assertTrue($report['ready']);
        $this->assertSame(0, $report['required_failures']);
        $this->assertSame(0, Artisan::call('nacs:input-abuse-baseline', ['--strict' => true]));
    }

    public function test_sensitive_student_writes_have_risk_based_throttles(): void
    {
        $routes = (string) file_get_contents(base_path('routes/student_portal.php'));

        foreach ([
            "throttle:nacs-sensitive-write')->name('students.grades.store')",
            "throttle:nacs-sensitive-write')->name('students.grades.destroy')",
            "throttle:nacs-sensitive-write')->name('students.attendance.store')",
            "throttle:nacs-sensitive-write')->name('students.finance.store')",
            "throttle:nacs-sensitive-write')->name('students.assignments.store')",
            "throttle:nacs-sensitive-write')->name('students.guardians.store')",
            "throttle:nacs-sensitive-write')->name('students.documents.store')",
        ] as $expected) {
            $this->assertStringContainsString($expected, $routes);
        }
    }

    public function test_sensitive_write_limiter_is_route_scoped(): void

    {

        $provider = (string) file_get_contents(app_path('Providers/AppServiceProvider.php'));



        $this->assertStringContainsString("RateLimiter::for('nacs-sensitive-write'", $provider);

        $this->assertStringContainsString("->by('nacs-sensitive-write|'.\$routeName.'|'.\$actor)", $provider);

    }



    // route-scoped named limiter regression

    public function test_confidential_upload_contracts_remain_allowlisted_or_external_only(): void
    {
        $photo = (string) file_get_contents(app_path('Http/Controllers/StudentProfilePhotoController.php'));
        $document = (string) file_get_contents(app_path('Http/Controllers/Admin/StudentDocumentController.php'));

        $this->assertStringContainsString("'mimes:jpg,jpeg,png,webp'", $photo);
        $this->assertStringContainsString("allow_local_fallback') === false", $document);
        $this->assertStringContainsString("google_cloud_storage", $document);
    }
}
