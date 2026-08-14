<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase24FinalReleaseAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_primary_public_routes_and_admin_login_smoke_successfully(): void
    {
        $routes = [
            'home',
            'about',
            'programs',
            'admissions',
            'announcements.index',
            'events.index',
            'gallery.index',
            'contact',
            'privacy',
            'faculty.index',
            'calendar.index',
            'documents.index',
            'media.index',
            'admissions.apply',
            'admissions.track',
            'sitemap',
            'robots',
            'admin.login',
        ];

        foreach ($routes as $name) {
            $this->get(route($name))->assertOk();
        }

        $this->get('/up')->assertOk();
    }

    public function test_release_hardening_css_is_last_on_every_public_layout_and_admin(): void
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

            $this->assertIsString($source, $layout);
            $this->assertStringContainsString('assets/phase24-release/release-hardening.css', $source, $layout);

            $hardening = strpos($source, 'assets/phase24-release/release-hardening.css');
            $headClose = stripos($source, '</head>');

            $this->assertNotFalse($hardening, $layout);
            $this->assertNotFalse($headClose, $layout);
            $this->assertLessThan($headClose, $hardening, $layout);
        }

        $admin = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        $this->assertIsString($admin);
        $this->assertStringContainsString('assets/phase24-release/release-hardening.css', $admin);
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
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertIsString($routes);
        $this->assertStringContainsString("middleware('throttle:5,10')->name('inquiries.store')", $routes);
        $this->assertStringContainsString("middleware('throttle:3,60')->name('admissions.apply.store')", $routes);
        $this->assertStringContainsString("middleware('throttle:5,10')->name('admissions.track.authenticate')", $routes);
        $this->assertStringContainsString("middleware('throttle:5,60')->name('admissions.documents.store')", $routes);
        $this->assertStringContainsString("middleware('throttle:5,1')->name('admin.login.store')", $routes);
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
        $badSequences = [
            "\xC3\xA2", // UTF-8 bytes for mojibake prefix "â"
            "\xC3\x82", // UTF-8 bytes for mojibake prefix "Â"
            "\xEF\xBF\xBD", // Unicode replacement character
        ];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $content = $file->getContents();
            $path = $file->getRelativePathname();

            foreach ($badSequences as $bad) {
                $this->assertStringNotContainsString($bad, $content, $path);
            }
        }
    }

    public function test_public_site_does_not_add_unsupported_senior_high_content(): void
    {
        $paths = [
            resource_path('views/home.blade.php'),
            resource_path('views/pages'),
            resource_path('views/announcements'),
            resource_path('views/events'),
            resource_path('views/gallery'),
            resource_path('views/faculty'),
            resource_path('views/calendar'),
            resource_path('views/documents'),
            resource_path('views/media'),
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $this->assertStringNotContainsString('Senior High', file_get_contents($path), $path);
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                $this->assertStringNotContainsString(
                    'Senior High',
                    $file->getContents(),
                    $file->getRelativePathname()
                );
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

    public function test_release_css_contains_focus_overflow_narrow_phone_and_reduced_motion_guards(): void
    {
        $css = file_get_contents(public_path('assets/phase24-release/release-hardening.css'));

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
