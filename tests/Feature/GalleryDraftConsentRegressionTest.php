<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryDraftConsentRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_save_unpublished_gallery_draft_without_consent(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post('/admin/gallery', [
            'title' => 'Presentation Candidate',
            'category' => 'School Life',
            'image' => $this->tinyPng('candidate.png'),
            'alt_text' => 'Presentation candidate image',
            'caption' => '',
            'sort_order' => 0,
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/gallery');

        $item = GalleryItem::query()->firstOrFail();
        $this->assertSame('draft', $item->workflow_status);
        $this->assertFalse($item->is_published);
        $this->assertNull($item->consent_confirmed_at);
    }

    public function test_manager_still_cannot_publish_gallery_photo_without_consent(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->from('/admin/gallery/create')->post('/admin/gallery', [
            'title' => 'Unapproved Public Photo',
            'category' => 'School Life',
            'image' => $this->tinyPng('public.png'),
            'alt_text' => 'Unapproved public image',
            'caption' => '',
            'sort_order' => 0,
            'is_published' => '1',
        ])->assertRedirect('/admin/gallery/create')->assertSessionHasErrors('consent_confirmed');

        $this->assertDatabaseCount('gallery_items', 0);
    }

    private function tinyPng(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'nacs-gallery-');
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Y9ZkRsAAAAASUVORK5CYII=',
            true
        );

        if ($path === false || $bytes === false || file_put_contents($path, $bytes) === false) {
            throw new \RuntimeException('Unable to create the GD-independent PNG test fixture.');
        }

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
