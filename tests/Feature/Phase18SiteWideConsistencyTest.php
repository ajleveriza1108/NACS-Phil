<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase18SiteWideConsistencyTest extends TestCase
{
    public function test_every_public_layout_loads_consistency_layer_after_phase_seventeen(): void
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

            $phase17 = strpos($source, 'assets/phase17-theme/site.css');
            $phase18 = strpos($source, 'assets/phase18-consistency/site-consistency.css');

            $this->assertNotFalse($phase17, $layout);
            $this->assertNotFalse($phase18, $layout);
            $this->assertGreaterThan($phase17, $phase18, $layout);
        }
    }

    public function test_shared_resource_hero_has_explicit_dark_surface_contrast(): void
    {
        $css = file_get_contents(public_path('assets/phase18-consistency/site-consistency.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.nacs11-public .nacs12-hero h1', $css);
        $this->assertStringContainsString('color:#fff!important', $css);
        $this->assertStringContainsString('.nacs11-public .nacs12-hero p', $css);
        $this->assertStringContainsString('color:#d0e0ed!important', $css);
        $this->assertStringContainsString('.nacs11-public .nacs12-hero .nacs12-kicker', $css);
        $this->assertStringContainsString('color:#f2c969!important', $css);
    }

    public function test_resources_dropdown_has_white_surface_and_dark_link_text(): void
    {
        $css = file_get_contents(public_path('assets/phase18-consistency/site-consistency.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.nacs16-resources__menu', $css);
        $this->assertStringContainsString('background:#fff!important', $css);
        $this->assertStringContainsString('.nacs16-resources__menu a,', $css);
        $this->assertStringContainsString('color:#102b4a!important', $css);
    }

    public function test_faculty_calendar_and_documents_share_the_guarded_resource_hero(): void
    {
        foreach ([
            'faculty/index.blade.php',
            'calendar/index.blade.php',
            'documents/index.blade.php',
        ] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertIsString($source, $view);
            $this->assertStringContainsString('class="nacs12-hero"', $source, $view);
            $this->assertStringContainsString("extends('layouts.public'", $source, $view);
        }
    }

    public function test_consistency_layer_keeps_narrow_phone_and_reduced_motion_rules(): void
    {
        $css = file_get_contents(public_path('assets/phase18-consistency/site-consistency.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
