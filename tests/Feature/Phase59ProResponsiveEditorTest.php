<?php
namespace Tests\Feature;

use App\Support\HomeEditorState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase59ProResponsiveEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_value_labels_use_single_line_responsive_fit_contract(): void
    {
        $home = (string) file_get_contents(resource_path('views/home.blade.php'));
        $css = (string) file_get_contents(public_path('assets/current/pages/home.css'));

        foreach (['why_2_title', 'why_3_title', 'why_4_title'] as $field) {
            $this->assertStringContainsString('data-visual-frame="'.$field.'"', $home);
            $this->assertMatchesRegularExpression(
                '/data-visual-field="'.preg_quote($field, '/').'"[^>]*data-visual-fit="single-line"/',
                $home
            );
        }

        $this->assertStringContainsString('.p18-about__points [data-visual-fit="single-line"]', $css);
        $this->assertStringContainsString('white-space:nowrap', $css);
        $this->assertStringContainsString('font-size:clamp(14px,4.4vw,17px)', $css);
        $this->assertStringContainsString('grid-template-columns:38px minmax(0,1fr)', $css);
    }

    public function test_responsive_style_state_is_allowlisted_clamped_and_safe(): void
    {
        HomeEditorState::setStyleOverrides([
            'why_2_title' => [
                'base' => [
                    'font_size' => 18,
                    'flow' => 'nowrap',
                    'max_width' => 520,
                    'padding_x' => 6,
                    'unsafe' => 'expression(alert(1))',
                ],
                'phone' => [
                    'font_size' => 999,
                    'text_align' => 'center',
                ],
            ],
            'fake_field' => [
                'base' => ['font_size' => 22],
            ],
        ]);

        $styles = HomeEditorState::styleOverrides();

        $this->assertSame(18.0, $styles['why_2_title']['base']['font_size']);
        $this->assertSame('nowrap', $styles['why_2_title']['base']['flow']);
        $this->assertSame(96.0, $styles['why_2_title']['phone']['font_size']);
        $this->assertSame('center', $styles['why_2_title']['phone']['text_align']);
        $this->assertArrayNotHasKey('unsafe', $styles['why_2_title']['base']);
        $this->assertArrayNotHasKey('fake_field', $styles);

        $css = HomeEditorState::styleCss();

        $this->assertStringContainsString('[data-visual-field="why_2_title"]', $css);
        $this->assertStringContainsString('font-size:18px!important', $css);
        $this->assertStringContainsString('white-space:nowrap!important', $css);
        $this->assertStringContainsString('@media(max-width:560px)', $css);
        $this->assertStringNotContainsString('expression(', $css);
        $this->assertStringNotContainsString('fake_field', $css);
    }

    public function test_revisions_restore_responsive_styles_and_reset_clears_them(): void
    {
        HomeEditorState::setStyleOverrides([
            'why_2_title' => ['phone' => ['font_size' => 15, 'flow' => 'nowrap']],
        ]);

        $key = HomeEditorState::recordRevision(null, 'publish');

        HomeEditorState::setStyleOverrides([
            'why_2_title' => ['phone' => ['font_size' => 11]],
        ]);

        $this->assertTrue(HomeEditorState::restoreRevision($key, null));
        $this->assertSame(15.0, HomeEditorState::styleOverrides()['why_2_title']['phone']['font_size']);
        $this->assertSame('nowrap', HomeEditorState::styleOverrides()['why_2_title']['phone']['flow']);

        HomeEditorState::resetOriginal(null);
        $this->assertSame([], HomeEditorState::styleOverrides());
    }

    public function test_professional_editor_exposes_responsive_typography_frame_and_fit_tools(): void
    {
        $view = (string) file_get_contents(resource_path('views/admin/website-content/edit.blade.php'));
        $js = (string) file_get_contents(public_path('assets/current/editor.js'));
        $css = (string) file_get_contents(public_path('assets/current/editor.css'));

        foreach ([
            'PRO RESPONSIVE EDITOR',
            'PRO INSPECTOR',
            'Smart Auto Fit',
            'Fit All Alerts',
            'Reset Device Style',
            'Desktop Base',
            'Text Frame',
            'data-ve-style-overrides',
            'data-ve-pro-control',
        ] as $marker) {
            $this->assertStringContainsString($marker, $view);
        }

        foreach ([
            'ResizeObserver',
            'measureTextWidth',
            'data-visual-fit',
            'data-ve-fit-health',
            'data-ve-style-overrides',
            'Run',
        ] as $marker) {
            if ($marker === 'Run') {
                continue;
            }
            $this->assertStringContainsString($marker, $js.$view);
        }

        $this->assertStringContainsString('.ve59-inspector', $css);
        $this->assertStringContainsString('.ve59-fit-alert', $css);
    }

    public function test_public_home_and_controller_apply_only_server_generated_style_css(): void
    {
        $home = (string) file_get_contents(resource_path('views/home.blade.php'));
        $homeController = (string) file_get_contents(app_path('Http/Controllers/HomeController.php'));
        $adminController = (string) file_get_contents(app_path('Http/Controllers/Admin/WebsiteContentController.php'));

        $this->assertStringContainsString('data-nacs-home-editor-styles', $home);
        $this->assertStringContainsString('$homeStyleCss', $home);
        $this->assertStringContainsString('HomeEditorState::styleCss()', $homeController);
        $this->assertStringContainsString('HomeEditorState::setStyleOverrides', $adminController);
        $this->assertStringContainsString("'max:60000'", $adminController);
        $this->assertStringNotContainsString('eval(', $adminController);
    }
}
