<?php

namespace Tests\Feature;

use App\Support\AdmissionsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase45MobileUxPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_navigation_is_grouped_accessible_and_blade_parser_safe(): void
    {
        $header = file_get_contents(resource_path('views/partials/public-header.blade.php'));
        $js = file_get_contents(public_path('assets/phase11-unified/public-theme.js'));

        $this->assertIsString($header);
        $this->assertIsString($js);

        $mobileStart = strpos($header, '<nav id="nacs11-mobile-nav"');
        $mobileEnd = strpos($header, '</nav>', $mobileStart);

        $this->assertNotFalse($mobileStart);
        $this->assertNotFalse($mobileEnd);

        $mobile = substr($header, $mobileStart, $mobileEnd - $mobileStart);

        foreach ([
            'nacs45-mobile-nav',
            'data-nacs45-mobile-group',
            'data-nacs45-mobile-group-toggle',
            'aria-controls="nacs45-mobile-about"',
            'aria-controls="nacs45-mobile-academics"',
            'aria-controls="nacs45-mobile-admissions"',
            'aria-controls="nacs45-mobile-news"',
            'aria-controls="nacs45-mobile-resources"',
            'data-nacs45-prefixes="/about,/faculty"',
            'data-nacs45-prefixes="/programs,/calendar"',
            'data-nacs45-prefixes="/admissions"',
            'data-nacs45-prefixes="/announcements,/events,/gallery,/media"',
            'data-nacs45-prefixes="/documents,/learning-tools"',
            '<span>{{ $nacsMediaGroupLabel }}</span>',
            "route('admissions.apply')",
            "route('admissions.track')",
        ] as $marker) {
            $this->assertStringContainsString($marker, $mobile);
        }

        $this->assertStringNotContainsString('@foreach', $mobile);
        $this->assertStringNotContainsString('@php', $mobile);
        $this->assertStringNotContainsString('@if', $mobile);
        $this->assertStringNotContainsString('data-nacs45-active=', $mobile);
        $this->assertSame(1, substr_count($mobile, '>Home</a>'));
        $this->assertSame(1, substr_count($mobile, "route('contact')"));

        foreach ([
            'NACS-Phil Phase 45 R1.6 - accessible grouped mobile navigation',
            'const normalizePath = (value)',
            'const currentPath = normalizePath(window.location.pathname)',
            'const markCurrentLocation = ()',
            'new URL(link.href, window.location.href)',
            'link.setAttribute("aria-current", "page")',
            'group.dataset.nacs45Prefixes',
            'currentPath.startsWith(`${prefix}/`)',
            'closeGroups(expanding ? group : null)',
            'toggle.setAttribute("aria-expanded"',
            'panel.hidden = !expanded',
            'openActiveGroup()',
        ] as $marker) {
            $this->assertStringContainsString($marker, $js);
        }
    }

    public function test_public_header_actually_renders_on_representative_public_routes(): void
    {
        $this->get(route('home'))->assertOk();
        $this->get(route('about'))->assertOk();
        $this->get(route('programs'))->assertOk();
        $this->get(route('admissions'))->assertOk();
        $this->get(route('announcements.index'))->assertOk();
        $this->get(route('contact'))->assertOk();
    }

    public function test_mobile_release_layer_centers_primary_h1_and_raises_supporting_type(): void
    {
        $css = file_get_contents(public_path('assets/phase24-release/release-hardening.css'));

        $this->assertIsString($css);

        foreach ([
            'NACS-Phil Phase 45 R1.5 - final real-device mobile hierarchy',
            'max-height:calc(100dvh - 76px)!important',
            '.nacs45-mobile-group__toggle',
            '@keyframes nacs45-submenu-enter',
            '.nacs-home-phase1 main h1',
            'text-align:center!important',
            '.nacs-home-phase1 .p18-quick-card small',
            'font-size:15px!important',
            '.about-phase2 .about-identity-strip article',
            '.programs-phase3 .programs-program__copy>p',
            '.admissions-phase4 .admissions-steps__number',
            'display:none!important',
            '.admissions-phase4 .admissions-steps p',
            'font-size:16px!important',
            '@media(prefers-reduced-motion:reduce)',
        ] as $marker) {
            $this->assertStringContainsString($marker, $css);
        }
    }

    public function test_admissions_normalizer_handles_single_and_double_encoded_apostrophes(): void
    {
        $single = (string) hex2bin('c3a2e282ace284a2');
        $double = (string) hex2bin('c383c2a2c3a2e2809ac2acc3a2e2809ec2a2');

        $normalized = AdmissionsContent::normalize([
            'single' => 'school'.$single.'s',
            'double' => 'school'.$double.'s',
        ]);

        $this->assertSame("school's", $normalized['single']);
        $this->assertSame("school's", $normalized['double']);
    }
}
