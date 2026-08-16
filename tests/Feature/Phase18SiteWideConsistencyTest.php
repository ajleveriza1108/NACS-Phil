<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase18SiteWideConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_representative_public_routes_use_current_semantic_page_bundles(): void
    {
        foreach ([
            '/' => 'home',
            '/about' => 'about',
            '/programs' => 'programs',
            '/admissions' => 'admissions',
            '/announcements' => 'news',
            '/events' => 'events',
            '/gallery' => 'gallery',
            '/contact' => 'contact',
            '/faculty' => 'public',
            '/calendar' => 'public',
            '/documents' => 'public',
        ] as $path => $bundle) {
            $this->get($path)
                ->assertOk()
                ->assertSee('assets/current/pages/'.$bundle.'.css', false)
                ->assertDontSee('/assets/phase', false);
        }
    }

    public function test_shared_resource_hero_has_explicit_dark_surface_contrast(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/public.css'));

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
        $css = file_get_contents(public_path('assets/current/pages/public.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.nacs16-resources__menu', $css);
        $this->assertStringContainsString('background:#fff!important', $css);
        $this->assertStringContainsString('.nacs16-resources__menu a,', $css);
        $this->assertStringContainsString('color:#102b4a!important', $css);
    }

    public function test_faculty_calendar_and_documents_use_the_current_public_bundle(): void
    {
        foreach ([
            'faculty/index.blade.php',
            'calendar/index.blade.php',
            'documents/index.blade.php',
        ] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertIsString($source, $view);
            $this->assertStringContainsString('class="nacs12-hero"', $source, $view);
            $this->assertStringContainsString("extends('layouts.site-current'", $source, $view);
            $this->assertStringContainsString("'assetBundle' => 'public'", $source, $view);
        }
    }

    public function test_current_public_bundle_keeps_narrow_phone_and_reduced_motion_rules(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/public.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
