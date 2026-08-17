<?php

namespace Tests\Feature;

use App\Support\AuthSessionSecurityBaseline;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase51AuthenticationSessionHardeningTest extends TestCase
{
    public function test_authentication_and_session_source_baseline_passes(): void
    {
        $report = app(AuthSessionSecurityBaseline::class)->summary();

        $this->assertTrue($report['ready']);
        $this->assertSame(0, $report['required_failures']);
        $this->assertSame(0, Artisan::call('nacs:auth-session-baseline', ['--strict' => true]));
    }

    public function test_privileged_two_factor_enforcement_is_wired_but_safely_disabled_by_default(): void
    {
        $this->assertFalse(config('nacs_security.privileged_2fa.required'));
        $this->assertSame(['super_admin', 'principal'], config('nacs_security.privileged_2fa.roles'));

        $bootstrap = (string) file_get_contents(base_path('bootstrap/app.php'));
        $routes = (string) file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString("'privileged_2fa' => RequirePrivilegedTwoFactor::class", $bootstrap);
        $this->assertStringContainsString("middleware(['auth', 'admin', 'privileged_2fa'])", $routes);
    }

    public function test_security_state_transitions_rotate_csrf_token(): void
    {
        $admin = (string) file_get_contents(app_path('Http/Controllers/Admin/SecurityController.php'));
        $portal = (string) file_get_contents(app_path('Http/Controllers/Portal/PasswordController.php'));

        $this->assertGreaterThanOrEqual(4, substr_count($admin, '$request->session()->regenerateToken();'));
        $this->assertStringContainsString('$request->session()->regenerateToken();', $portal);
    }
}
