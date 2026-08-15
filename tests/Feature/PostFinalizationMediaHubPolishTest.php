<?php

namespace Tests\Feature;

use App\Models\FacebookMediaItem;
use App\Models\GalleryItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFinalizationMediaHubPolishTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_hub_combines_approved_photos_recorded_videos_and_live_links(): void
    {
        GalleryItem::create([
            'title' => 'Approved Campus Photo',
            'category' => 'Campus',
            'image_path' => 'gallery/approved-campus.jpg',
            'alt_text' => 'Approved NACS-Phil campus photograph.',
            'is_published' => true,
            'sort_order' => 1,
            'consent_confirmed_at' => now(),
            'workflow_status' => 'published',
        ]);

        foreach ([
            ['Recorded School Program', 'video', '123456789'],
            ['School Livestream', 'live', '987654321'],
        ] as [$title, $type, $id]) {
            FacebookMediaItem::create([
                'title' => $title,
                'media_type' => $type,
                'facebook_url' => "https://www.facebook.com/nacsphil/videos/{$id}/",
                'status' => 'published',
                'published_at' => now(),
                'public_confirmed_at' => now(),
            ]);
        }

        $this->get(route('media.index'))
            ->assertOk()
            ->assertSee('Media Hub')
            ->assertSee('Approved Campus Photo')
            ->assertSee('Recorded School Program')
            ->assertSee('School Livestream')
            ->assertSee('Approved Photos')
            ->assertSee('Recorded Videos')
            ->assertSee('Live Broadcasts');
    }

    public function test_media_hub_filters_keep_photo_video_and_live_surfaces_distinct(): void
    {
        GalleryItem::create([
            'title' => 'Filter Photo',
            'category' => 'Campus',
            'image_path' => 'gallery/filter-photo.jpg',
            'alt_text' => 'Filter test photo.',
            'is_published' => true,
            'sort_order' => 1,
            'consent_confirmed_at' => now(),
            'workflow_status' => 'published',
        ]);

        foreach ([
            ['Filter Recorded Video', 'video', '123456789'],
            ['Filter Live Stream', 'live', '987654321'],
        ] as [$title, $type, $id]) {
            FacebookMediaItem::create([
                'title' => $title,
                'media_type' => $type,
                'facebook_url' => "https://www.facebook.com/nacsphil/videos/{$id}/",
                'status' => 'published',
                'published_at' => now(),
                'public_confirmed_at' => now(),
            ]);
        }

        $this->get(route('media.index', ['type' => 'photos']))
            ->assertOk()->assertSee('Filter Photo')->assertDontSee('Filter Recorded Video')->assertDontSee('Filter Live Stream');

        $this->get(route('media.index', ['type' => 'videos']))
            ->assertOk()->assertDontSee('Filter Photo')->assertSee('Filter Recorded Video')->assertDontSee('Filter Live Stream');

        $this->get(route('media.index', ['type' => 'live']))
            ->assertOk()->assertDontSee('Filter Photo')->assertDontSee('Filter Recorded Video')->assertSee('Filter Live Stream');
    }

    public function test_homepage_fallback_logo_has_explicit_no_crop_override(): void
    {
        $css = (string) file_get_contents(public_path('assets/phase18-home/home.css'));

        $this->assertStringContainsString('.nacs-home-phase1 .p18-hero__visual-frame .p18-hero__fallback img', $css);
        $this->assertStringContainsString('min-height:0!important', $css);
        $this->assertStringContainsString('object-fit:contain!important', $css);
        $this->assertStringContainsString('max-height:136px!important', $css);
    }

    public function test_navigation_and_homepage_expose_the_unified_media_hub(): void
    {
        $header = (string) file_get_contents(resource_path('views/partials/public-header.blade.php'));
        $footer = (string) file_get_contents(resource_path('views/partials/public-footer.blade.php'));
        $home = (string) file_get_contents(resource_path('views/home.blade.php'));

        $this->assertSame(2, substr_count($header, "route('media.index')"));
        $this->assertSame(2, substr_count($header, 'Media Hub'));
        $this->assertSame(1, substr_count($footer, "route('media.index')"));
        $this->assertSame(1, substr_count($footer, 'Media Hub'));
        $this->assertStringContainsString('href="{{ route(\'media.index\') }}">Explore Media', $home);
    }
}
