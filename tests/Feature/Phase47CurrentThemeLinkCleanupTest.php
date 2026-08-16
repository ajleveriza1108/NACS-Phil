<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase47CurrentThemeLinkCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_source_uses_no_obsolete_layout_or_phase_asset_path(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $source = File::get($file->getPathname());
            $path = str_replace('\\', '/', $file->getPathname());

            $this->assertDoesNotMatchRegularExpression(
                "/@extends\\(\\s*['\"]layouts\\.(?:home-phase1|about-phase2|programs-phase3|admissions-phase4|news-phase5|events-phase6|gallery-phase7|contact-phase8|admissions-portal-phase9c|public)['\"]/i",
                $source,
                $path
            );
            $this->assertDoesNotMatchRegularExpression(
                "/assets\\/(?:phase[0-9]|phase17-theme)/i",
                $source,
                $path
            );
        }

        foreach ([app_path(), config_path()] as $root) {
            foreach (File::allFiles($root) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                $this->assertDoesNotMatchRegularExpression(
                    "/assets\\/(?:phase[0-9]|phase17-theme)/i",
                    File::get($file->getPathname()),
                    $file->getPathname()
                );
            }
        }
    }

    public function test_obsolete_layout_loader_files_are_physically_removed(): void
    {
        foreach ([
            'home-phase1.blade.php','about-phase2.blade.php','programs-phase3.blade.php',
            'admissions-phase4.blade.php','news-phase5.blade.php','events-phase6.blade.php',
            'gallery-phase7.blade.php','contact-phase8.blade.php',
            'admissions-portal-phase9c.blade.php','public.blade.php',
        ] as $layout) {
            $this->assertFileDoesNotExist(resource_path('views/layouts/'.$layout));
        }
    }

    public function test_representative_public_routes_have_no_phase_runtime_urls(): void
    {
        foreach ([
            '/', '/about', '/programs', '/admissions', '/announcements', '/events',
            '/gallery', '/contact', '/privacy', '/faculty', '/calendar', '/documents',
            '/media', '/admissions/apply', '/admissions/track',
        ] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertDontSee('/assets/phase', false)
                ->assertSee('/assets/current/', false);
        }
    }

    public function test_current_gallery_shell_preserves_lightbox_runtime_markup(): void
    {
        $this->get('/gallery')
            ->assertOk()
            ->assertSee('data-g-lightbox', false)
            ->assertSee('data-g-close', false)
            ->assertSee('data-g-prev', false)
            ->assertSee('data-g-next', false);
    }

    public function test_current_link_wiring_uses_programs_application_and_calendar_routes(): void
    {
        $home = File::get(resource_path('views/home.blade.php'));
        $header = File::get(resource_path('views/partials/public-header.blade.php'));

        $this->assertStringContainsString("href=\"{{ route('programs') }}\"", $home);
        $this->assertStringContainsString("href=\"{{ route('admissions.apply') }}\"", $home);
        $this->assertStringContainsString(
            "nacs11-header__cta\" href=\"{{ route('admissions.apply') }}\"",
            $header
        );
        $this->assertStringContainsString('data-nacs45-prefixes="/programs,/calendar"', $header);
        $this->assertStringNotContainsString('/programs,/academic-calendar', $header);
    }

    public function test_current_semantic_bundles_and_media_exist(): void
    {
        foreach ([
            'home','about','programs','admissions','news','events','gallery',
            'contact','admissions-portal','public',
        ] as $bundle) {
            $this->assertFileExists(public_path('assets/current/pages/'.$bundle.'.css'));
            $this->assertFileExists(public_path('assets/current/pages/'.$bundle.'.js'));
        }

        $this->assertFileExists(public_path('assets/current/admin.css'));
        $this->assertFileExists(public_path('assets/current/admin.js'));
        $this->assertDirectoryExists(public_path('assets/current/media'));
    }
}
