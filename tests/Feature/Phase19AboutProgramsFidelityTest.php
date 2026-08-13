<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase19AboutProgramsFidelityTest extends TestCase
{
    public function test_about_and_programs_load_phase_nineteen_after_consistency_layer(): void
    {
        foreach (['about-phase2.blade.php', 'programs-phase3.blade.php'] as $layout) {
            $source = file_get_contents(resource_path('views/layouts/'.$layout));

            $this->assertIsString($source, $layout);

            $phase18 = strpos($source, 'assets/phase18-consistency/site-consistency.css');
            $phase19 = strpos($source, 'assets/phase19-about-programs/fidelity.css');

            $this->assertNotFalse($phase18, $layout);
            $this->assertNotFalse($phase19, $layout);
            $this->assertGreaterThan($phase18, $phase19, $layout);
        }
    }

    public function test_about_and_programs_keep_existing_cms_driven_content_structures(): void
    {
        $about = file_get_contents(resource_path('views/pages/about.blade.php'));
        $programs = file_get_contents(resource_path('views/pages/programs.blade.php'));

        $this->assertIsString($about);
        $this->assertIsString($programs);

        $this->assertStringContainsString("SiteContent::valuesFor('about'", $about);
        $this->assertStringContainsString("SiteContent::valuesFor('programs'", $programs);

        $this->assertStringContainsString("['mission_title']", $about);
        $this->assertStringContainsString("['vision_title']", $about);
        $this->assertStringContainsString("['leader_message']", $about);

        $this->assertStringContainsString("['preschool_title']", $programs);
        $this->assertStringContainsString("['elementary_title']", $programs);
        $this->assertStringContainsString("['junior_title']", $programs);
    }

    public function test_programs_page_does_not_add_unsupported_senior_high(): void
    {
        $programs = file_get_contents(resource_path('views/pages/programs.blade.php'));

        $this->assertIsString($programs);
        $this->assertStringNotContainsString('Senior High', $programs);
    }

    public function test_about_and_programs_have_no_known_mojibake_sequences(): void
    {
        foreach (['pages/about.blade.php', 'pages/programs.blade.php'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertIsString($source, $view);
            $this->assertStringNotContainsString('â', $source, $view);
            $this->assertStringNotContainsString('Â', $source, $view);
        }
    }

    public function test_fidelity_css_protects_dark_faith_sections_and_responsive_breakpoints(): void
    {
        $css = file_get_contents(public_path('assets/phase19-about-programs/fidelity.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.about-phase2 .about-faith h2', $css);
        $this->assertStringContainsString('.programs-phase3 .programs-faith h2', $css);
        $this->assertStringContainsString('color:#fff!important', $css);
        $this->assertStringContainsString('@media(max-width:1180px)', $css);
        $this->assertStringContainsString('@media(max-width:900px)', $css);
        $this->assertStringContainsString('@media(max-width:620px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
