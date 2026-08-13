<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase11ThemeConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_share_one_phase_eleven_shell(): void
    {
        foreach ([
            '/',
            '/about',
            '/programs',
            '/admissions',
            '/admissions/apply',
            '/admissions/track',
            '/announcements',
            '/events',
            '/gallery',
            '/contact',
            '/privacy',
        ] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('data-nacs11-header', false)
                ->assertSee('data-nacs11-footer', false)
                ->assertSee('assets/phase11-unified/public-theme.css', false)
                ->assertSee('assets/phase11-unified/public-theme.js', false);
        }
    }

    public function test_privacy_page_uses_the_unified_modern_visual_language(): void
    {
        $this->get('/privacy')
            ->assertOk()
            ->assertSee('nacs11-privacy-hero', false)
            ->assertSee('nacs11-privacy-card', false)
            ->assertSee('Admissions documents stay private');
    }

    public function test_public_header_has_consistent_navigation_and_mobile_control(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('nacs11-desktop-nav', false)
            ->assertSee('data-nacs11-menu-button', false)
            ->assertSee('data-nacs11-mobile-nav', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_known_mojibake_is_not_present_in_blade_views(): void
    {
        $bad = [
            hex2bin('c3a2e282ace2809d'),
            hex2bin('c382c2b7'),
            hex2bin('c3a2c593e2809c'),
        ];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());

            foreach ($bad as $needle) {
                $this->assertStringNotContainsString(
                    $needle,
                    $contents,
                    'Mojibake remains in '.$file->getRelativePathname()
                );
            }
        }
    }
}