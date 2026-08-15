<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Support\AboutContent;
use App\Support\AdmissionsContent;
use App\Support\ProgramsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PageImageManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_manager_can_upload_hero_and_leadership_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = AboutContent::defaults();
        $payload['hero_image'] = $this->tinyPng('about-hero.png');
        $payload['hero_image_authorized'] = '1';
        $payload['leadership_image'] = $this->tinyPng('leadership.png');
        $payload['leadership_image_authorized'] = '1';

        $this->actingAs($admin)->patch('/admin/about-content', $payload)->assertSessionHasNoErrors();

        $content = SiteContent::valuesFor('about', AboutContent::defaults());
        $this->assertStringStartsWith('site/about/', $content['hero_image_path']);
        $this->assertStringStartsWith('site/about/', $content['leadership_image_path']);
        Storage::disk('public')->assertExists($content['hero_image_path']);
        Storage::disk('public')->assertExists($content['leadership_image_path']);
    }

    public function test_programs_page_manager_can_upload_program_images(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = ProgramsContent::defaults();
        foreach (['hero', 'preschool', 'elementary', 'junior'] as $slot) {
            $payload[$slot.'_image'] = $this->tinyPng($slot.'.png');
            $payload[$slot.'_image_authorized'] = '1';
        }

        $this->actingAs($admin)->patch('/admin/programs-content', $payload)->assertSessionHasNoErrors();

        $content = SiteContent::valuesFor('programs', ProgramsContent::defaults());
        foreach (['hero', 'preschool', 'elementary', 'junior'] as $slot) {
            $this->assertStringStartsWith('site/programs/', $content[$slot.'_image_path']);
            Storage::disk('public')->assertExists($content[$slot.'_image_path']);
        }
    }

    public function test_admissions_page_manager_can_upload_hero_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = AdmissionsContent::defaults();
        $payload['hero_image'] = $this->tinyPng('admissions.png');
        $payload['hero_image_authorized'] = '1';

        $this->actingAs($admin)->patch('/admin/admissions-content', $payload)->assertSessionHasNoErrors();

        $content = SiteContent::valuesFor('admissions', AdmissionsContent::defaults());
        $this->assertStringStartsWith('site/admissions/', $content['hero_image_path']);
        Storage::disk('public')->assertExists($content['hero_image_path']);
    }

    private function tinyPng(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'nacs-page-image-');
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZkRsAAAAASUVORK5CYII=',
            true
        );

        if ($path === false || $bytes === false || file_put_contents($path, $bytes) === false) {
            throw new \RuntimeException('Unable to create the GD-independent page image test fixture.');
        }

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
