<?php

namespace Tests\Feature;

use App\Support\AuthorizationSecurityBaseline;
use App\Support\SecuritySurfaceInventory;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class Phase49AuthorizationIdorHardeningTest extends TestCase
{
    public function test_authorization_and_idor_source_baseline_passes(): void
    {
        $report = app(AuthorizationSecurityBaseline::class)->summary();

        $this->assertTrue($report['ready']);
        $this->assertSame(0, $report['required_failures']);
        $this->assertSame(0, Artisan::call('nacs:authorization-baseline', ['--strict' => true]));
    }

    public function test_future_api_remains_default_off_until_shared_authorization_exists(): void
    {
        $future = SecuritySurfaceInventory::future();

        $this->assertSame('future_only', $future['mobile_api']['status']);
        $this->assertFalse(config('nacs_security.future.mobile_api.enabled'));
    }

    public function test_student_access_source_keeps_explicit_default_deny(): void
    {
        $source = (string) file_get_contents(app_path('Support/StudentAccess.php'));

        $this->assertStringContainsString("return \$query->whereRaw('1 = 0');", $source);
        $this->assertStringContainsString("->where('teacher_id', \$user->id)", $source);
        $this->assertStringContainsString("->where('status', 'active')", $source);
    }
}
