<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class Phase56VisualUxPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_tools_loads_the_shared_public_shell_and_phase56_polish(): void
    {
        $response = $this->get(route('learning-tools.index'));

        $response
            ->assertOk()
            ->assertSee('assets/current/pages/public.css', false)
            ->assertSee('assets/current/pages/learning-tools.css', false)
            ->assertSee('assets/current/phase56/visual-polish.css', false)
            ->assertSee('assets/current/pages/public.js', false)
            ->assertSee('assets/current/pages/learning-tools.js', false)
            ->assertSee('Dictionary &amp; Grammar', false);
    }

    public function test_phase56_css_raises_homepage_readability_and_removes_resources_caret(): void
    {
        $source = (string) file_get_contents(public_path('assets/current/phase56/visual-polish.css'));

        foreach ([
            '.nacs11-menu > summary::after',
            '.nacs16-resources > summary::after',
            '.nacs11-menu > summary::before',
            '.nacs16-resources > summary::before',
            'content: none !important',
            '.lt-hero h1',
            'color: #fff !important',
            '-webkit-text-fill-color: #fff !important',
            '.nacs-home-phase1 .p18-about__points li',
            'font-size: 17px !important',
            '.nacs-home-phase1 .p18-values-band strong',
            '.nacs-home-phase1 .p18-values-band small',
            '.nacs-home-phase1 .p18-news-card h3',
            '.nacs-home-phase1 .p18-event-card h3',
            'font-size: 16px !important',
            '.nacs-home-phase1 .p18-news-card p',
            '.nacs-home-phase1 .p18-event-card__meta',
            'font-size: 13px !important',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }

    public function test_learning_tools_layout_is_developed_and_does_not_stretch_dictionary_card(): void
    {
        $source = (string) file_get_contents(public_path('assets/current/pages/learning-tools.css'));

        foreach ([
            'align-items: start;',
            'min-height: clamp(300px, 35vw, 430px);',
            'font-size: clamp(44px, 6.5vw, 82px);',
            '.lt-card::before',
            '.lt-result',
            '@media (max-width: 900px)',
            '@media (max-width: 560px)',
        ] as $marker) {
            $this->assertStringContainsString($marker, $source);
        }
    }

    public function test_footer_exposes_learning_tools_as_a_school_resource(): void
    {
        $source = (string) file_get_contents(resource_path('views/partials/public-footer.blade.php'));

        $this->assertStringContainsString("route('learning-tools.index')", $source);
        $this->assertStringContainsString('Dictionary &amp; Grammar', $source);
    }

    public function test_news_visual_has_clean_ascii_label(): void
    {
        $source = (string) file_get_contents(public_path('assets/current/media/c5236371e29a-news-visual.svg'));

        $this->assertStringContainsString('NACS-PHIL - NEWS + ANNOUNCEMENTS', $source);
        $this->assertStringNotContainsString('NACS-PHIL Â· NEWS + ANNOUNCEMENTS', $source);
    }

    public function test_current_public_view_and_asset_text_has_no_common_mojibake(): void
    {
        $roots = [
            resource_path('views'),
            public_path('assets/current'),
        ];

        $extensions = ['php', 'blade.php', 'css', 'js', 'svg', 'md', 'txt'];
        $markers = ['Â', 'Ã', 'â€', 'â€™', 'â€œ', 'â€', '�'];
        $issues = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                $normalized = str_replace('\\', '/', $path);
                $matchesExtension = false;

                foreach ($extensions as $extension) {
                    if (str_ends_with(strtolower($normalized), '.'.strtolower($extension))) {
                        $matchesExtension = true;
                        break;
                    }
                }

                if (! $matchesExtension) {
                    continue;
                }

                $contents = (string) file_get_contents($path);

                foreach ($markers as $marker) {
                    if (str_contains($contents, $marker)) {
                        $issues[] = $normalized.' contains '.$marker;
                    }
                }
            }
        }

        $this->assertSame([], $issues, implode(PHP_EOL, $issues));
    }
}
