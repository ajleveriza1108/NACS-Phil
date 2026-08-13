<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase15LaunchReadinessTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_principal_can_open_launch_readiness_screen(): void
    {
        $principal = $this->staff('principal');

        $this->actingAs($principal)
            ->get('/admin/launch-readiness')
            ->assertOk()
            ->assertSee('Launch Readiness')
            ->assertSee('School approval')
            ->assertSee('Content &amp; consent', false)
            ->assertSee('Final school sign-off');
    }

    public function test_teacher_cannot_open_launch_readiness_screen(): void
    {
        $teacher = $this->staff('teacher');

        $this->actingAs($teacher)
            ->get('/admin/launch-readiness')
            ->assertForbidden();
    }

    public function test_school_settings_are_reflected_in_launch_readiness(): void
    {
        $principal = $this->staff('principal');

        foreach ([
            'school_name' => 'Noel Academy Christian of Sariaya Philippines, Inc.',
            'short_name' => 'NACS-Phil',
            'current_school_year' => '2026-2027',
            'address' => 'Sariaya, Quezon',
            'office_hours' => 'Monday-Friday',
            'privacy_email' => 'privacy@example.test',
            'email' => 'office@example.test',
        ] as $key => $value) {
            SchoolSetting::query()->create([
                'key' => $key,
                'value' => $value,
                'group' => 'test',
                'is_public' => true,
                'updated_by_user_id' => $principal->id,
            ]);
        }

        $this->actingAs($principal)
            ->get('/admin/launch-readiness')
            ->assertOk()
            ->assertSee('Noel Academy Christian of Sariaya Philippines, Inc.')
            ->assertSee('2026-2027')
            ->assertSee('At least one public phone/email contact is configured.');
    }

    public function test_development_preview_banner_is_not_rendered_in_production(): void
    {
        $original = app()->environment();

        app()->detectEnvironment(fn () => 'production');

        try {
            $this->get('/')
                ->assertOk()
                ->assertDontSee('Development preview');
        } finally {
            app()->detectEnvironment(fn () => $original);
        }
    }

    public function test_launch_readiness_css_is_responsive(): void
    {
        $css = file_get_contents(public_path('assets/phase15-launch/launch.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media (max-width: 900px)', $css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
    }
}
