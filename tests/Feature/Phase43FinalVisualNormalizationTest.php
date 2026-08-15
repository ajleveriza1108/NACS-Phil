<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase43FinalVisualNormalizationTest extends TestCase
{
    public function test_homepage_uses_consistent_inline_svg_quick_link_icons(): void
    {
        $home = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertIsString($home);
        $this->assertSame(5, substr_count($home, 'class="p43-quick-icon"'));
        $this->assertStringNotContainsString('&#9633;', $home);
        $this->assertStringNotContainsString('&#8943;', $home);
    }

    public function test_release_layer_contains_final_responsive_visual_normalization(): void
    {
        $css = file_get_contents(public_path('assets/phase24-release/release-hardening.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('NACS-Phil Phase 43 - Final visual normalization', $css);
        $this->assertStringContainsString('.nacs-home-phase1 .p18-hero__actions', $css);
        $this->assertStringContainsString('max-width:230px!important', $css);
        $this->assertStringContainsString('.nacs11-mobile-nav__inner a.is-active:not(.nacs11-button)', $css);
        $this->assertStringContainsString('background:rgba(242,201,105,.10)!important', $css);
        $this->assertStringContainsString('.about-phase2 .about-hero h1', $css);
        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
    }

    public function test_primary_public_routes_and_shared_navigation_contract_remain_declared(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $header = file_get_contents(resource_path('views/partials/public-header.blade.php'));

        $this->assertIsString($routes);
        $this->assertIsString($header);

        foreach ([
            "Route::get('/', HomeController::class)->name('home');",
            "Route::view('/about', 'pages.about')->name('about');",
            "Route::view('/programs', 'pages.programs')->name('programs');",
            "Route::view('/admissions', 'pages.admissions')->name('admissions');",
            "Route::view('/contact', 'pages.contact')->name('contact');",
        ] as $routeDeclaration) {
            $this->assertStringContainsString($routeDeclaration, $routes);
        }

        foreach ([
            "['route' => 'home'",
            "['route' => 'about'",
            "['route' => 'programs'",
            "['route' => 'admissions'",
            "['route' => 'contact'",
            "route('faculty.index')",
            "route('calendar.index')",
            "route('documents.index')",
            "route('media.index')",
        ] as $navigationContract) {
            $this->assertStringContainsString($navigationContract, $header);
        }
    }
}
