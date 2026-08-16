<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase19AboutProgramsFidelityTest extends TestCase
{
    public function test_about_and_programs_use_their_current_semantic_bundles(): void
    {
        foreach ([
            'pages/about.blade.php' => 'about',
            'pages/programs.blade.php' => 'programs',
        ] as $view => $bundle) {
            $source = file_get_contents(resource_path('views/'.$view));

            $this->assertIsString($source, $view);
            $this->assertStringContainsString("extends('layouts.site-current'", $source, $view);
            $this->assertStringContainsString("'assetBundle' => '".$bundle."'", $source, $view);
            $this->assertFileExists(public_path('assets/current/pages/'.$bundle.'.css'));
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

    public function test_current_about_and_programs_bundles_keep_fidelity_rules(): void
    {
        $about = file_get_contents(public_path('assets/current/pages/about.css'));
        $programs = file_get_contents(public_path('assets/current/pages/programs.css'));

        foreach ([$about, $programs] as $css) {
            $this->assertIsString($css);
            $this->assertStringContainsString('color:#fff!important', $css);
            $this->assertStringContainsString('@media(max-width:620px)', $css);
            $this->assertStringContainsString('@media(max-width:380px)', $css);
            $this->assertStringContainsString('prefers-reduced-motion', $css);
        }
    }
}
