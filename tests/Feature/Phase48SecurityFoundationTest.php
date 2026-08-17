<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Support\SecurityBaseline;
use App\Support\SecurityEventLogger;
use App\Support\SecuritySurfaceInventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase48SecurityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_source_security_baseline_passes_and_future_surfaces_remain_inactive(): void
    {
        $report = app(SecurityBaseline::class)->summary();

        $this->assertTrue($report['source_ready']);
        $this->assertSame(0, $report['required_failures']);
        $this->assertSame('source', $report['scope']);

        foreach (SecuritySurfaceInventory::future() as $surface) {
            $this->assertSame('future_only', $surface['status']);
        }

        $this->assertSame(0, Artisan::call('nacs:security-baseline', ['--strict' => true]));
    }

    public function test_production_security_check_does_not_falsely_mark_local_testing_ready(): void
    {
        $report = app(SecurityBaseline::class)->summary(true);

        $this->assertTrue($report['production_requested']);
        $this->assertGreaterThan(0, $report['required_failures']);
        $this->assertSame(1, Artisan::call('nacs:security-baseline', [
            '--production' => true,
            '--strict' => true,
        ]));
    }

    public function test_security_logger_uses_allowlisted_metadata_and_never_request_payload_secrets(): void
    {
        $request = Request::create('/admin/login', 'POST', [
            'email' => 'private@example.test',
            'password' => 'NeverLogThis#123',
            'token' => 'secret-token',
        ], [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'NACS Security Test Agent',
        ]);

        $context = app(SecurityEventLogger::class)->context($request, [
            'status' => 429,
            'reason' => 'rate_limit',
            'email' => 'must-not-log@example.test',
            'password' => 'must-not-log',
            'token' => 'must-not-log',
        ]);

        $this->assertSame(429, $context['status']);
        $this->assertSame('rate_limit', $context['reason']);
        $this->assertArrayNotHasKey('email', $context);
        $this->assertArrayNotHasKey('password', $context);
        $this->assertArrayNotHasKey('token', $context);
        $this->assertNotSame('203.0.113.10', $context['ip_hash']);
        $this->assertNotSame('NACS Security Test Agent', $context['user_agent_hash']);
    }

    public function test_cross_teacher_student_access_is_denied_for_record_photo_and_report(): void
    {
        $principal = $this->staff('principal', 'phase48-principal@nacs.test');
        $teacherA = $this->staff('teacher', 'phase48-a@nacs.test');
        $teacherB = $this->staff('teacher', 'phase48-b@nacs.test');

        $studentA = $this->student('NACS-SEC-0001', 'Alpha', $principal);
        $studentB = $this->student('NACS-SEC-0002', 'Beta', $principal);

        $studentA->assignments()->create([
            'teacher_id' => $teacherA->id,
            'school_year' => '2026-2027',
            'subject' => 'Science',
            'is_adviser' => false,
            'can_manage_profile' => true,
            'can_manage_grades' => true,
            'can_manage_attendance' => true,
            'status' => 'active',
            'approved_by' => $principal->id,
            'approved_at' => now(),
        ]);

        $studentB->assignments()->create([
            'teacher_id' => $teacherB->id,
            'school_year' => '2026-2027',
            'subject' => 'Science',
            'is_adviser' => false,
            'can_manage_profile' => true,
            'can_manage_grades' => true,
            'can_manage_attendance' => true,
            'status' => 'active',
            'approved_by' => $principal->id,
            'approved_at' => now(),
        ]);

        $this->actingAs($teacherA)
            ->get(route('admin.students.show', $studentB))
            ->assertForbidden();

        $this->actingAs($teacherA)
            ->get(route('admin.students.photo', $studentB))
            ->assertForbidden();

        $this->actingAs($teacherA)
            ->get(route('admin.students.report-card', $studentB))
            ->assertForbidden();

        $this->actingAs($teacherA)
            ->get(route('admin.students.show', $studentA))
            ->assertOk();
    }

    public function test_admin_password_change_requires_symbols_and_revokes_other_sessions_contract(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
            'role' => 'super_admin',
            'email' => 'phase48-admin@nacs.test',
            'password' => Hash::make('Current#Password123'),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.security.password'), [
                'current_password' => 'Current#Password123',
                'password' => 'NewPassword12345',
                'password_confirmation' => 'NewPassword12345',
            ])
            ->assertSessionHasErrors('password');

        $this->actingAs($admin)
            ->patch(route('admin.security.password'), [
                'current_password' => 'Current#Password123',
                'password' => 'New#Password12345',
                'password_confirmation' => 'New#Password12345',
            ])
            ->assertSessionHasNoErrors();

        $admin->refresh();

        $this->assertTrue(Hash::check('New#Password12345', $admin->password));
        $this->assertNotNull($admin->password_changed_at);
    }

    public function test_sensitive_current_routes_keep_abuse_controls_and_upload_allowlists(): void
    {
        $web = file_get_contents(base_path('routes/web.php'));
        $portal = file_get_contents(base_path('routes/student_portal.php'));
        $photo = file_get_contents(app_path('Http/Controllers/StudentProfilePhotoController.php'));
        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));

        $this->assertStringContainsString("turnstile:admin_login", $web);
        $this->assertStringContainsString("turnstile:admissions_apply", $web);
        $this->assertStringContainsString("turnstile:admissions_track", $web);
        $this->assertStringContainsString("middleware('throttle:5,10')->name('security.password')", $web);

        $this->assertStringContainsString("middleware('throttle:10,10')->name('students.photo.store')", $portal);
        $this->assertStringContainsString("middleware('throttle:30,1')->name('students.report-card')", $portal);

        $this->assertStringContainsString("'mimes:jpg,jpeg,png,webp'", $photo);
        $this->assertStringContainsString("'max:'.\$maxKb", $photo);
        $this->assertStringContainsString("'dimensions:min_width='.\$minWidth", $photo);

        $this->assertStringContainsString('LogSecurityEvents::class', $bootstrap);
        $this->assertSame('daily', config('logging.channels.security.driver'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('json', config('session.serialization'));
    }

    private function staff(string $role, string $email): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
            'role' => $role,
            'email' => $email,
        ]);
    }

    private function student(string $number, string $firstName, User $creator): Student
    {
        return Student::create([
            'student_number' => $number,
            'first_name' => $firstName,
            'last_name' => 'Security',
            'date_of_birth' => '2014-05-07',
            'grade_level' => 'Grade 6',
            'school_year' => '2026-2027',
            'status' => 'active',
            'classification' => 'confidential',
            'created_by' => $creator->id,
        ]);
    }
}
