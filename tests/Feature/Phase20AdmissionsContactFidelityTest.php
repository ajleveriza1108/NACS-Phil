<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase20AdmissionsContactFidelityTest extends TestCase
{
    public function test_admissions_and_contact_use_current_semantic_bundles(): void
    {
        foreach ([
            'pages/admissions.blade.php' => 'admissions',
            'pages/contact.blade.php' => 'contact',
        ] as $view => $bundle) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertIsString($source, $view);
            $this->assertStringContainsString("extends('layouts.site-current'", $source, $view);
            $this->assertStringContainsString("'assetBundle' => '".$bundle."'", $source, $view);
            $this->assertFileExists(public_path('assets/current/pages/'.$bundle.'.css'));
        }
    }

    public function test_admissions_preserves_cms_process_privacy_and_private_portal_links(): void
    {
        $source = file_get_contents(resource_path('views/pages/admissions.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("SiteContent::valuesFor('admissions'", $source);
        $this->assertStringContainsString('@foreach([1,2,3,4] as $number)', $source);
        $this->assertStringContainsString("['step_'.\$number.'_title']", $source);
        $this->assertStringContainsString("['requirement_'.\$number.'_title']", $source);
        $this->assertStringContainsString("['faq_'.\$number.'_q']", $source);
        $this->assertStringContainsString("route('privacy')", $source);
        $this->assertStringContainsString("route('admissions.apply')", $source);
        $this->assertStringContainsString("route('admissions.track')", $source);
    }

    public function test_contact_preserves_secure_inquiry_form_contract(): void
    {
        $source = file_get_contents(resource_path('views/pages/contact.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("SiteContent::valuesFor('contact'", $source);
        $this->assertStringContainsString("route('inquiries.store')", $source);
        $this->assertStringContainsString('@csrf', $source);
        $this->assertStringContainsString('name="website"', $source);
        $this->assertStringContainsString('name="privacy_consent"', $source);
        $this->assertStringContainsString('name="guardian_name"', $source);
        $this->assertStringContainsString('name="level_interested"', $source);
        $this->assertStringContainsString('Junior High School', $source);
        $this->assertStringNotContainsString('Senior High', $source);
    }

    public function test_current_admissions_and_contact_bundles_keep_dark_surface_contrast(): void
    {
        foreach (['admissions', 'contact'] as $bundle) {
            $css = file_get_contents(public_path('assets/current/pages/'.$bundle.'.css'));
            $this->assertIsString($css);
            $this->assertStringContainsString('color:#fff!important', $css);
            $this->assertStringContainsString('color:#d2e1ec!important', $css);
        }
    }

    public function test_current_bundles_cover_desktop_tablet_phone_narrow_and_reduced_motion(): void
    {
        $css = file_get_contents(public_path('assets/current/pages/admissions.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media(max-width:1180px)', $css);
        $this->assertStringContainsString('@media(max-width:1024px)', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
        $this->assertStringContainsString('@media(max-width:480px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
