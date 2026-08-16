<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase45MotionInteractionSystemTest extends TestCase
{
    public function test_motion_assets_exist_and_use_restrained_shared_tokens(): void
    {
        $css = file_get_contents(public_path('assets/phase45-motion/motion.css'));
        $js = file_get_contents(public_path('assets/phase45-motion/motion.js'));

        $this->assertIsString($css);
        $this->assertIsString($js);

        foreach ([
            '--nacs-motion-fast:150ms',
            '--nacs-motion-base:220ms',
            '--nacs-motion-slow:380ms',
            '--nacs-motion-lift:-3px',
            'transform:translateY(var(--nacs-motion-lift))',
            'scale(1.025)',
            '.nacs11-desktop-nav>a::after',
            'nacs-mobile-menu-enter',
            'nacs-details-enter',
            'nacs-lightbox-image',
            '[data-nacs-sticky-table]',
            '.nacs-skeleton',
            '@media (prefers-reduced-motion:reduce)',
        ] as $marker) {
            $this->assertStringContainsString($marker, $css);
        }

        $this->assertStringNotContainsString('transition:all', str_replace(' ', '', $css));
        $this->assertStringNotContainsString('scale(1.1)', $css);
        $this->assertStringNotContainsString('infinite alternate', $css);

        foreach ([
            'IntersectionObserver',
            'prefers-reduced-motion: reduce',
            'nacs-motion-public',
            'nacs-motion-admin',
            'nacs-motion-auth',
            'wrapper.dataset.nacsStickyTable = "1"',
            'Math.min(index * 45, 180)',
        ] as $marker) {
            $this->assertStringContainsString($marker, $js);
        }

        $this->assertStringNotContainsString('setInterval(', $js);
        $this->assertStringNotContainsString('requestAnimationFrame(count', $js);
    }

    public function test_public_admin_portal_and_auth_layouts_load_shared_motion_assets(): void
    {
        $layouts = [
            'resources/views/layouts/public.blade.php',
            'resources/views/layouts/home-phase1.blade.php',
            'resources/views/layouts/about-phase2.blade.php',
            'resources/views/layouts/programs-phase3.blade.php',
            'resources/views/layouts/admissions-phase4.blade.php',
            'resources/views/layouts/news-phase5.blade.php',
            'resources/views/layouts/events-phase6.blade.php',
            'resources/views/layouts/gallery-phase7.blade.php',
            'resources/views/layouts/contact-phase8.blade.php',
            'resources/views/layouts/admissions-portal-phase9c.blade.php',
            'resources/views/admin/layouts/app.blade.php',
            'resources/views/portal/layout.blade.php',
            'resources/views/admin/auth/login.blade.php',
            'resources/views/admin/auth/two-factor.blade.php',
        ];

        foreach ($layouts as $relative) {
            $source = file_get_contents(base_path($relative));

            $this->assertIsString($source, $relative);
            $this->assertSame(1, substr_count($source, "assets/phase45-motion/motion.css"), $relative);
            $this->assertSame(1, substr_count($source, "assets/phase45-motion/motion.js"), $relative);
        }
    }

    public function test_resilient_error_pages_remain_independent_from_motion_assets(): void
    {
        $layout = file_get_contents(resource_path('views/errors/layout.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringNotContainsString('phase45-motion', $layout);
        $this->assertStringContainsString('phase42-launch/errors.css', $layout);
    }
}
