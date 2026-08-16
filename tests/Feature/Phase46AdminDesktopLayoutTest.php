<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase46AdminDesktopLayoutTest extends TestCase
{
    public function test_desktop_admin_uses_one_sidebar_column(): void
    {
        $css = file_get_contents(public_path('assets/phase13-admin/admin.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('Phase 46 R2.0.4 - unified desktop admin shell', $css);
        $this->assertStringContainsString('grid-template-columns: 272px minmax(0, 1fr)', $css);
        $this->assertStringContainsString('margin-left: 0', $css);
        $this->assertStringContainsString('font-size: 13px', $css);
    }

    public function test_r204_override_is_desktop_only(): void
    {
        $css = file_get_contents(public_path('assets/phase13-admin/admin.css'));

        $marker = strpos($css, 'Phase 46 R2.0.4 - unified desktop admin shell');
        $this->assertNotFalse($marker);

        $repair = substr($css, $marker);
        $this->assertStringContainsString('@media (min-width: 1101px)', $repair);
        $this->assertStringNotContainsString('@media (max-width:', $repair);
        $this->assertStringNotContainsString('position: fixed', $repair);
    }
}
