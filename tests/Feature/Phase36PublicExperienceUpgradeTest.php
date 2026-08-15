<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase36PublicExperienceUpgradeTest extends TestCase
{
    public function test_phase_thirty_six_learning_media_is_bundled_and_local_only(): void
    {
        $css = file_get_contents(public_path('assets/phase36-experience/site.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('images/preschool-learning.webp', $css);
        $this->assertStringContainsString('images/elementary-learning.webp', $css);
        $this->assertStringContainsString('images/junior-high-learning.webp', $css);
        $this->assertStringNotContainsString('http://', $css);
        $this->assertStringNotContainsString('https://', $css);

        foreach ([
            'preschool-learning.webp',
            'elementary-learning.webp',
            'junior-high-learning.webp',
        ] as $asset) {
            $this->assertFileExists(public_path('assets/phase36-experience/images/'.$asset));
        }
    }

    public function test_home_and_programs_load_phase_thirty_six_after_release_hardening(): void
    {
        foreach ([
            resource_path('views/layouts/home-phase1.blade.php'),
            resource_path('views/layouts/programs-phase3.blade.php'),
        ] as $layoutPath) {
            $layout = file_get_contents($layoutPath);

            $this->assertIsString($layout);

            $release = strpos($layout, 'assets/phase24-release/release-hardening.css');
            $phase36 = strpos($layout, 'assets/phase36-experience/site.css');

            $this->assertNotFalse($release);
            $this->assertNotFalse($phase36);
            $this->assertGreaterThan($release, $phase36);
        }
    }

    public function test_stock_photography_is_transparently_marked_as_illustrative(): void
    {
        $css = file_get_contents(public_path('assets/phase36-experience/site.css'));
        $manifest = file_get_contents(base_path('THIRD_PARTY_MEDIA.md'));

        $this->assertIsString($css);
        $this->assertIsString($manifest);
        $this->assertStringContainsString('Illustrative learning photo', $css);
        $this->assertStringContainsString('do not depict NACS-Phil students', $manifest);
        $this->assertStringContainsString('7978262', $manifest);
        $this->assertStringContainsString('18506736', $manifest);
        $this->assertStringContainsString('32279011', $manifest);
    }

    public function test_shared_mobile_navigation_closes_without_unwanted_escape_focus_stealing(): void
    {
        $javascript = file_get_contents(public_path('assets/phase11-unified/public-theme.js'));

        $this->assertIsString($javascript);
        $this->assertStringContainsString('const wasExpanded', $javascript);
        $this->assertStringContainsString('restoreFocus && wasExpanded', $javascript);
        $this->assertStringContainsString('pointerdown', $javascript);
    }

    public function test_release_hardening_keeps_anchor_targets_and_forced_colors_accessible(): void
    {
        $css = file_get_contents(public_path('assets/phase24-release/release-hardening.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString(':target', $css);
        $this->assertStringContainsString('@media(forced-colors:active)', $css);
    }
}
