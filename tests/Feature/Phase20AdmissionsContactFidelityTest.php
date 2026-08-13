<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase20AdmissionsContactFidelityTest extends TestCase
{
    public function test_admissions_and_contact_load_phase_twenty_after_consistency_layer(): void
    {
        foreach (['admissions-phase4.blade.php', 'contact-phase8.blade.php'] as $layout) {
            $source = file_get_contents(resource_path('views/layouts/'.$layout));

            $this->assertIsString($source, $layout);

            $phase18 = strpos($source, 'assets/phase18-consistency/site-consistency.css');
            $phase20 = strpos($source, 'assets/phase20-admissions-contact/fidelity.css');

            $this->assertNotFalse($phase18, $layout);
            $this->assertNotFalse($phase20, $layout);
            $this->assertGreaterThan($phase18, $phase20, $layout);
        }
    }

    public function test_admissions_preserves_cms_process_privacy_and_private_portal_links(): void
    {
        $source = file_get_contents(resource_path('views/pages/admissions.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("SiteContent::valuesFor('admissions'", $source);
        $this->assertStringContainsString('@foreach([1,2,3,4] as $number)', $source);
        $this->assertStringContainsString('[\'step_\'.$number.\'_title\']', $source);
        $this->assertStringContainsString('[\'requirement_\'.$number.\'_title\']', $source);
        $this->assertStringContainsString('[\'faq_\'.$number.\'_q\']', $source);
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

    public function test_dark_admissions_and_contact_surfaces_have_explicit_light_text_contracts(): void
    {
        $css = file_get_contents(public_path('assets/phase20-admissions-contact/fidelity.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.admissions-phase4 .admissions-dates h1', $css);
        $this->assertStringContainsString('.admissions-phase4 .admissions-final-cta h1', $css);
        $this->assertStringContainsString('.contact-phase8 .contact-privacy-card h3', $css);
        $this->assertStringContainsString('color:#fff!important', $css);
        $this->assertStringContainsString('color:#d2e1ec!important', $css);
    }

    public function test_phase_twenty_css_covers_desktop_tablet_phone_narrow_and_reduced_motion(): void
    {
        $css = file_get_contents(public_path('assets/phase20-admissions-contact/fidelity.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media(max-width:1180px)', $css);
        $this->assertStringContainsString('@media(max-width:1024px)', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
        $this->assertStringContainsString('@media(max-width:480px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
