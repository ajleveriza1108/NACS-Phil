<?php

namespace Tests\Feature;

use App\Support\AdmissionsContent;
use Tests\TestCase;

class Phase44MobileContentPolishTest extends TestCase
{
    public function test_admissions_defaults_use_plain_ascii_punctuation(): void
    {
        $defaults = AdmissionsContent::defaults();

        $this->assertSame(
            "Follow the school's approved submission process. If an online application workflow is enabled, it remains handled by the existing admissions system.",
            $defaults['step_3_text']
        );
        $this->assertStringContainsString('Grades 1-6', $defaults['faq_1_a']);
        $this->assertStringContainsString('Grades 7-10', $defaults['faq_1_a']);

        foreach ($defaults as $value) {
            if (! is_string($value)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression('/[\x{2018}\x{2019}\x{201C}\x{201D}\x{2013}\x{2014}]/u', $value);
        }
    }

    public function test_admissions_normalizer_repairs_legacy_mojibake_without_database_mutation(): void
    {
        $badApostrophe = (string) hex2bin('c3a2e282ace284a2');
        $badDash = (string) hex2bin('c3a2e282ace2809c');
        $curlyApostrophe = (string) hex2bin('e28099');

        $normalized = AdmissionsContent::normalize([
            'sentence' => 'Follow the school'.$badApostrophe.'s approved process.',
            'grades' => 'Grades 1'.$badDash.'6',
            'curly' => 'school'.$curlyApostrophe.'s',
            'untouched' => 'Current admissions information',
        ]);

        $this->assertSame("Follow the school's approved process.", $normalized['sentence']);
        $this->assertSame('Grades 1-6', $normalized['grades']);
        $this->assertSame("school's", $normalized['curly']);
        $this->assertSame('Current admissions information', $normalized['untouched']);
    }

    public function test_admissions_view_normalizes_content_and_uses_one_step_number_treatment(): void
    {
        $source = file_get_contents(resource_path('views/pages/admissions.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString('AdmissionsContent::normalize(', $source);
        $this->assertStringContainsString("SiteContent::valuesFor('admissions'", $source);
        $this->assertStringContainsString('class="admissions-steps__icon"', $source);
        $this->assertStringNotContainsString('class="admissions-steps__number"', $source);
    }

    public function test_release_layer_scopes_real_device_centering_and_density_to_public_components(): void
    {
        $css = file_get_contents(public_path('assets/phase24-release/release-hardening.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('NACS-Phil Phase 44 - Real-device mobile content alignment', $css);
        $this->assertStringContainsString('.nacs-home-phase1 .p18-hero .hero__content', $css);
        $this->assertStringContainsString('.admissions-phase4 .admissions-hero__status', $css);
        $this->assertStringContainsString('.admissions-phase4 .admissions-info-strip article', $css);
        $this->assertStringContainsString('.admissions-phase4 .admissions-steps article', $css);
        $this->assertStringContainsString('min-height:auto!important', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
        $this->assertStringContainsString('@media(max-width:480px)', $css);
        $this->assertStringNotContainsString('h3{text-align:center', $css);
    }
}
