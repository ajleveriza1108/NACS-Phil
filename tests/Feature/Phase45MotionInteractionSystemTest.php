<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase45MotionInteractionSystemTest extends TestCase
{
    public function test_current_bundles_keep_restrained_shared_motion_tokens(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/home.css'));
        $js = file_get_contents(public_path('assets/current/pages/home.js'));

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
            'Math.min(index * 45, 180)',
        ] as $marker) {
            $this->assertStringContainsString($marker, $js);
        }

        $this->assertStringNotContainsString('setInterval(', $js);
    }

    public function test_active_layouts_do_not_load_phase_named_motion_assets(): void
    {
        foreach ([
            'resources/views/layouts/site-current.blade.php',
            'resources/views/admin/layouts/app.blade.php',
            'resources/views/portal/layout.blade.php',
            'resources/views/admin/auth/login.blade.php',
            'resources/views/admin/auth/two-factor.blade.php',
        ] as $relative) {
            $source = file_get_contents(base_path($relative));

            $this->assertIsString($source, $relative);
            $this->assertStringNotContainsString('assets/phase', $source, $relative);
        }
    }

    public function test_resilient_error_pages_use_current_asset_paths(): void
    {
        $layout = file_get_contents(resource_path('views/errors/layout.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringNotContainsString('assets/phase', $layout);
        $this->assertStringContainsString('assets/current/', $layout);
    }
}
