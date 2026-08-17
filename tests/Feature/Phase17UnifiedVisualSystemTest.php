<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase17UnifiedVisualSystemTest extends TestCase
{
    public function test_public_layout_uses_only_semantic_current_bundle_loader(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/site-current.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString('assets/current/pages/', $layout);
        $this->assertStringNotContainsString('assets/phase', $layout);
    }

    public function test_admin_layout_uses_current_admin_visual_bundle(): void
    {
        $source = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('assets/current/admin.css', $source);
        $this->assertStringContainsString('assets/current/admin.js', $source);
        $this->assertStringNotContainsString("asset('assets/phase", $source);
    }

    public function test_official_bundled_school_logo_is_a_current_media_fallback(): void
    {
        $model = file_get_contents(app_path('Models/SchoolSetting.php'));

        $this->assertIsString($model);
        $this->assertStringContainsString('assets/current/media/', $model);
        $this->assertStringContainsString('nacs-official-logo.png', $model);
        $this->assertStringContainsString('officialBrandingApproved', $model);
        $this->assertStringContainsString('return filled($path)', $model);

        preg_match("/asset\('([^']*nacs-official-logo\.png)'\)/", $model, $matches);
        $this->assertArrayHasKey(1, $matches);
        $this->assertFileExists(public_path($matches[1]));
    }

    public function test_current_home_bundle_contains_visual_tokens_and_breakpoints(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/home.css'));

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
