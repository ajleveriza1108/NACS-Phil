<?php

namespace Tests\Feature;

use App\Models\SchoolSetting;
use Tests\TestCase;

class Phase17UnifiedVisualSystemTest extends TestCase
{
    public function test_all_public_layouts_load_phase_seventeen_theme_last(): void
    {
        $layouts = [
            'home-phase1.blade.php',
            'about-phase2.blade.php',
            'programs-phase3.blade.php',
            'admissions-phase4.blade.php',
            'news-phase5.blade.php',
            'events-phase6.blade.php',
            'gallery-phase7.blade.php',
            'contact-phase8.blade.php',
            'admissions-portal-phase9c.blade.php',
            'public.blade.php',
        ];

        foreach ($layouts as $layout) {
            $source = file_get_contents(resource_path('views/layouts/'.$layout));

            $this->assertIsString($source);
            $this->assertStringContainsString('assets/phase17-theme/site.css', $source, $layout);
        }
    }

    public function test_admin_layout_loads_phase_seventeen_admin_visual_system(): void
    {
        $source = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('assets/phase17-theme/admin.css', $source);
    }

    public function test_official_bundled_school_logo_is_the_safe_fallback(): void
    {
        $this->assertFileExists(public_path('assets/phase17-theme/nacs-official-logo.png'));

        $model = file_get_contents(app_path('Models/SchoolSetting.php'));

        $this->assertIsString($model);
        $this->assertStringContainsString('assets/phase17-theme/nacs-official-logo.png', $model);
        $this->assertStringContainsString('officialBrandingApproved', $model);
        $this->assertStringContainsString('return filled($path)', $model);
    }

    public function test_visual_system_contains_required_mockup_tokens_and_breakpoints(): void
    {
        $css = file_get_contents(public_path('assets/phase17-theme/site.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('--n17-navy-950:#031a39', $css);
        $this->assertStringContainsString('--n17-gold:#e4ae43', $css);
        $this->assertStringContainsString('@media(max-width:1050px)', $css);
        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }

    public function test_shared_header_and_footer_keep_school_resources_and_enrollment_cta(): void
    {
        $header = file_get_contents(resource_path('views/partials/public-header.blade.php'));
        $footer = file_get_contents(resource_path('views/partials/public-footer.blade.php'));

        $this->assertIsString($header);
        $this->assertIsString($footer);
        $this->assertStringContainsString('Enroll Now', $header);
        $this->assertStringContainsString('Resources', $header);
        $this->assertStringContainsString('Faculty &amp; Staff', $header);
        $this->assertStringContainsString('Academic Calendar', $header);
        $this->assertStringContainsString('School Resources', $footer);
    }
}
