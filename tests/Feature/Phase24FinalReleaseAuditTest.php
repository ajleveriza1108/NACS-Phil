<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase24FinalReleaseAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_primary_public_routes_and_admin_login_smoke_successfully(): void
    {
        $routes = [
            'home','about','programs','admissions','announcements.index','events.index',
            'gallery.index','contact','privacy','faculty.index','calendar.index',
            'documents.index','media.index','admissions.apply','admissions.track',
            'sitemap','robots','admin.login',
        ];

        foreach ($routes as $name) {
            $this->get(route($name))->assertOk();
        }

        $this->get('/up')->assertOk();
    }

    public function test_current_release_hardening_is_inside_semantic_public_and_admin_bundles(): void
    {
        $homeCss = file_get_contents(public_path('assets/current/pages/home.css'));
        $adminCss = file_get_contents(public_path('assets/current/admin.css'));
        $siteLayout = file_get_contents(resource_path('views/layouts/site-current.blade.php'));
        $adminLayout = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        foreach ([$homeCss, $adminCss, $siteLayout, $adminLayout] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('overflow-x:clip', $homeCss);
        $this->assertStringContainsString(':focus-visible', $homeCss);
        $this->assertStringContainsString('prefers-reduced-motion', $homeCss);
        $this->assertStringContainsString('assets/current/pages/', $siteLayout);
        $this->assertStringContainsString('assets/current/admin.css', $adminLayout);
        $this->assertStringNotContainsString("asset('assets/phase", $siteLayout);
        $this->assertStringNotContainsString("asset('assets/phase", $adminLayout);
    }

    public function test_browser_security_headers_and_sensitive_admissions_no_store_are_active(): void
    {
        $home = $this->get(route('home'));
        $home->assertHeader('X-Content-Type-Options', 'nosniff');
        $home->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $home->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $home->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');

        $csp = (string) $home->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);

        foreach (['admissions.apply', 'admissions.track'] as $routeName) {
            $response = $this->get(route($routeName));
            $cacheControl = (string) $response->headers->get('Cache-Control');
            $this->assertStringContainsString('no-store', $cacheControl, $routeName);
            $this->assertStringContainsString('private', $cacheControl, $routeName);
        }
    }

    public function test_sensitive_write_routes_retain_throttling_contracts(): void
    {
        foreach ([
            'inquiries.store' => 'throttle:5,10',
            'admissions.apply.store' => 'throttle:3,60',
            'admissions.track.authenticate' => 'throttle:5,10',
            'admissions.documents.store' => 'throttle:5,60',
            'admin.login.store' => 'throttle:5,1',
        ] as $routeName => $requiredThrottle) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, $routeName);
            $this->assertContains($requiredThrottle, $route->gatherMiddleware(), $routeName);
        }
    }

    public function test_navigation_resources_are_not_duplicated_and_accessibility_shell_is_present(): void
    {
        $header = file_get_contents(resource_path('views/partials/public-header.blade.php'));
        $admin = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        $this->assertIsString($header);
        $this->assertIsString($admin);

        foreach ([
            "route('faculty.index')" => 'Faculty & Staff',
            "route('calendar.index')" => 'Academic Calendar',
            "route('documents.index')" => 'Documents',
            "route('media.index')" => 'Live & Videos',
        ] as $needle => $label) {
            $this->assertSame(2, substr_count($header, $needle), $label);
        }

        $this->assertStringContainsString('Skip to content', $header);
        $this->assertStringContainsString('aria-label="Primary navigation"', $header);
        $this->assertStringContainsString('aria-label="Mobile navigation"', $header);
        $this->assertStringContainsString('aria-expanded="false"', $header);
        $this->assertStringContainsString('aria-controls="nacs11-mobile-nav"', $header);
        $this->assertStringContainsString('Skip to administration content', $admin);
        $this->assertStringContainsString('id="admin-main"', $admin);
    }

    public function test_public_blade_views_have_no_known_mojibake_or_replacement_characters(): void
    {
        $badSequences = ["\xC3\xA2", "\xC3\x82", "\xEF\xBF\xBD"];

        foreach (File::allFiles(resource_path('views')) as $file) {
            foreach ($badSequences as $bad) {
                $this->assertStringNotContainsString($bad, $file->getContents(), $file->getRelativePathname());
            }
        }
    }

    public function test_public_site_does_not_add_unsupported_senior_high_content(): void
    {
        $paths = [
            resource_path('views/home.blade.php'), resource_path('views/pages'),
            resource_path('views/announcements'), resource_path('views/events'),
            resource_path('views/gallery'), resource_path('views/faculty'),
            resource_path('views/calendar'), resource_path('views/documents'),
            resource_path('views/media'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $this->assertStringNotContainsString('Senior High', file_get_contents($path), $path);
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                $this->assertStringNotContainsString('Senior High', $file->getContents(), $file->getRelativePathname());
            }
        }
    }

    public function test_launch_readiness_checks_facebook_media_confirmation_and_real_playback_review(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Admin/LaunchReadinessController.php'));

        $this->assertIsString($controller);
        $this->assertStringContainsString('FacebookMediaItem', $controller);
        $this->assertStringContainsString('Facebook media public/embed confirmation', $controller);
        $this->assertStringContainsString('public_confirmed_at', $controller);
        $this->assertStringContainsString('one real public Facebook recorded video', $controller);
        $this->assertStringContainsString('Facebook Live/replay link', $controller);
    }

    public function test_current_release_bundle_contains_focus_overflow_narrow_phone_and_reduced_motion_guards(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/home.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('overflow-x:clip', $css);
        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('outline:3px solid #f2c969', $css);
        $this->assertStringContainsString('@media(max-width:520px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion:reduce', $css);
    }

    public function test_final_release_checklist_exists_and_protects_production_secrets_and_private_admissions_files(): void
    {
        $checklist = file_get_contents(base_path('FINAL_RELEASE_CHECKLIST.md'));

        $this->assertIsString($checklist);
        $this->assertStringContainsString('APP_ENV=production', $checklist);
        $this->assertStringContainsString('APP_DEBUG=false', $checklist);
        $this->assertStringContainsString('APP_URL=https://<final-domain>', $checklist);
        $this->assertStringContainsString('Do not publish the local development SQLite database', $checklist);
        $this->assertStringContainsString('admissions documents must remain private', $checklist);
        $this->assertStringContainsString('automated Phase 24 QA to pass', $checklist);
    }
}
