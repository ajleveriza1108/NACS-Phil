<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase18HomepageMockupFidelityTest extends TestCase
{
    public function test_homepage_contains_the_five_parent_facing_quick_links(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('>Admissions<', $source);
        $this->assertStringContainsString('>Academic Calendar<', $source);
        $this->assertStringContainsString('>Faculty &amp; Staff<', $source);
        $this->assertStringContainsString('>School Documents<', $source);
        $this->assertStringContainsString('>Parent Inquiry<', $source);
    }

    public function test_homepage_does_not_invent_senior_high_or_unverified_statistics(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertIsString($source);
        $this->assertStringNotContainsString('Senior High', $source);
        $this->assertStringNotContainsString('18:1', $source);
        $this->assertStringNotContainsString('100%', $source);
    }

    public function test_homepage_keeps_dynamic_school_content_and_approved_media_only(): void
    {
        $source = file_get_contents(resource_path('views/home.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('$homeContent[\'hero_heading\']', $source);
        $this->assertStringContainsString('$galleryItems->first()', $source);
        $this->assertStringContainsString('SchoolSetting::logoUrl()', $source);
        $this->assertStringContainsString('Approved school photography', $source);
    }

    public function test_homepage_uses_current_home_bundle(): void
    {
        $view = file_get_contents(resource_path('views/home.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/site-current.blade.php'));

        $this->assertIsString($view);
        $this->assertIsString($layout);
        $this->assertStringContainsString("extends('layouts.site-current'", $view);
        $this->assertStringContainsString("'assetBundle' => 'home'", $view);
        $this->assertStringContainsString('assets/current/pages/', $layout);
    }

    public function test_current_home_css_covers_desktop_tablet_phone_and_320_width(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/home.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media(max-width:1180px)', $css);
        $this->assertStringContainsString('@media(max-width:900px)', $css);
        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
