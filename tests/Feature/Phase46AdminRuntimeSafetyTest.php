<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase46AdminRuntimeSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_crash_when_local_sis_students_table_has_not_been_migrated(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Schema::dropIfExists('students');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('School Manager')
            ->assertSee('Local database setup is incomplete')
            ->assertSee('No database migration was performed automatically');
    }

    public function test_staff_login_keeps_compact_desktop_contract_and_touch_safe_controls(): void
    {
        $css = file_get_contents(public_path('assets/phase39-auth/auth.css'));
        $view = file_get_contents(resource_path('views/admin/auth/login.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($view);

        $this->assertStringContainsString('Phase 46 R2.0.3 compact staff login', $css);
        $this->assertStringContainsString('width: min(100%, 560px)', $css);
        $this->assertStringContainsString('min-height: 48px', $css);
        $this->assertStringContainsString('font-size: clamp(1.75rem, 3vw, 2.2rem)', $css);
        $this->assertStringContainsString('width="64" height="64"', $view);
    }
}
