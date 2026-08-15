<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase39Phase40ReleaseGateTest extends TestCase
{
    public function test_phase_thirty_nine_keeps_manual_visual_and_staging_acceptance_explicit(): void
    {
        $contents = file_get_contents(base_path('PHASE39_FINAL_VISUAL_STAGING_ACCEPTANCE.md'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('Manual visual acceptance', $contents);
        $this->assertStringContainsString('approximately 320px width', $contents);
        $this->assertStringContainsString('Passing these checks does **not** mark visual acceptance complete', $contents);
        $this->assertStringContainsString('Student & Parent Portal', $contents);
    }

    public function test_phase_forty_keeps_production_deployment_host_specific_and_inactive(): void
    {
        $contents = file_get_contents(base_path('PHASE40_PRODUCTION_LAUNCH.md'));
        $template = file_get_contents(base_path('.github/deployment-templates/production-deploy.yml'));

        $this->assertIsString($contents);
        $this->assertStringContainsString('not** activated by the cumulative installer', $contents);
        $this->assertStringContainsString('Deploy the exact approved `main` commit', $contents);
        $this->assertStringContainsString('remains an inactive template', $contents);
        $this->assertStringContainsString('# name:', $template);
    }
}
