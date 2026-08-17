<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase23ResourcesPrivacyAdmissionsPolishTest extends TestCase
{
    public function test_public_and_admissions_portal_use_current_semantic_polish_bundles(): void
    {
        $public = file_get_contents(public_path('assets/current/pages/public.css'));
        $portal = file_get_contents(public_path('assets/current/pages/admissions-portal.css'));

        $this->assertIsString($public);
        $this->assertIsString($portal);

        $this->assertStringContainsString('.nacs11-public .nacs12-hero h1', $public);
        $this->assertStringContainsString('.adm9-body .adm9-hero h1', $portal);

        foreach ([$public, $portal] as $css) {
            $this->assertStringContainsString('color:#fff!important', $css);
            $this->assertStringContainsString('@media(max-width:760px)', $css);
            $this->assertStringContainsString('@media(max-width:520px)', $css);
            $this->assertStringContainsString('@media(max-width:380px)', $css);
            $this->assertStringContainsString('prefers-reduced-motion', $css);
        }
    }

    public function test_faculty_calendar_and_documents_keep_working_data_contracts(): void
    {
        $faculty = file_get_contents(resource_path('views/faculty/index.blade.php'));
        $calendar = file_get_contents(resource_path('views/calendar/index.blade.php'));
        $documents = file_get_contents(resource_path('views/documents/index.blade.php'));

        $this->assertStringContainsString('$departments', $faculty);
        $this->assertStringContainsString('$profiles', $faculty);
        $this->assertStringContainsString('Storage::url($profile->photo_path)', $faculty);

        $this->assertStringContainsString('$schoolYears', $calendar);
        $this->assertStringContainsString('$categories', $calendar);
        $this->assertStringContainsString('$entries->links()', $calendar);

        $this->assertStringContainsString('$documents', $documents);
        $this->assertStringContainsString("route('documents.download'", $documents);
        $this->assertStringContainsString('$documents->links()', $documents);
    }

    public function test_privacy_page_discloses_facebook_hosted_video_behavior_and_keeps_legal_review_notice(): void
    {
        $privacy = file_get_contents(resource_path('views/pages/privacy.blade.php'));

        $this->assertIsString($privacy);
        $this->assertStringContainsString('Facebook-hosted videos and livestreams', $privacy);
        $this->assertStringContainsString('actual video remains hosted and streamed by Facebook', $privacy);
        $this->assertStringContainsString('Meta/Facebook privacy and cookie practices may apply', $privacy);
        $this->assertStringContainsString('qualified Philippine privacy counsel before public launch', $privacy);
        $this->assertStringContainsString('Admissions documents stay private', $privacy);
    }

    public function test_admissions_portal_keeps_security_consent_tracking_and_private_document_contracts(): void
    {
        $apply = file_get_contents(resource_path('views/admissions/apply.blade.php'));
        $track = file_get_contents(resource_path('views/admissions/track.blade.php'));
        $status = file_get_contents(resource_path('views/admissions/status.blade.php'));
        $receipt = file_get_contents(resource_path('views/admissions/receipt.blade.php'));

        $this->assertStringContainsString('@csrf', $apply);
        $this->assertStringContainsString('name="website"', $apply);
        $this->assertStringContainsString('name="privacy_consent"', $apply);
        $this->assertStringContainsString('name="application_consent"', $apply);

        $this->assertStringContainsString('reference_code', $track);
        $this->assertStringContainsString('access_code', $track);
        $this->assertStringContainsString('@csrf', $track);

        $this->assertStringContainsString('$canUploadDocuments', $status);
        $this->assertStringContainsString('accept=".pdf,.jpg,.jpeg,.png"', $status);
        $this->assertStringContainsString('maximum 5 MB', $status);
        $this->assertStringContainsString('document_consent', $status);

        $this->assertStringContainsString('{{ $accessCode }}', $receipt);
        $this->assertStringContainsString('shown only on this receipt', $receipt);
    }

    public function test_admissions_status_has_no_known_mojibake_sequences(): void
    {
        $status = file_get_contents(resource_path('views/admissions/status.blade.php'));

        $this->assertIsString($status);
        $this->assertStringNotContainsString('â', $status);
        $this->assertStringNotContainsString('Â', $status);
        $this->assertStringContainsString('&ldquo;Awaiting documents.&rdquo;', $status);
    }

    public function test_phase_twenty_three_css_covers_contrast_and_responsive_breakpoints(): void
    {
        $css = file_get_contents(public_path('assets/phase23-final-polish/polish.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.nacs11-public .nacs12-hero h1', $css);
        $this->assertStringContainsString('.nacs11-public .nacs11-privacy-card--wide h2', $css);
        $this->assertStringContainsString('.adm9-body .adm9-hero h1', $css);
        $this->assertStringContainsString('color:#fff!important', $css);
        $this->assertStringContainsString('@media(max-width:980px)', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
        $this->assertStringContainsString('@media(max-width:520px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
