<?php

namespace Tests\Feature;

use App\Models\FacebookMediaItem;
use App\Models\User;
use App\Support\FacebookMediaUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase22FacebookMediaHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_media_page_shows_published_items_with_facebook_player_but_not_drafts(): void
    {
        $published = FacebookMediaItem::create([
            'title' => 'Sunday Worship Replay',
            'media_type' => 'video',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
            'status' => 'published',
            'published_at' => now(),
            'public_confirmed_at' => now(),
        ]);

        FacebookMediaItem::create([
            'title' => 'Private Draft',
            'media_type' => 'live',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/999999999/',
            'status' => 'draft',
        ]);

        $this->get(route('media.index'))
            ->assertOk()
            ->assertSee('Sunday Worship Replay')
            ->assertDontSee('Private Draft')
            ->assertSee('<iframe', false)
            ->assertSee($published->embedUrl())
            ->assertSee('Watch on Facebook');
    }

    public function test_facebook_url_helper_accepts_only_https_facebook_hosts(): void
    {
        $good = 'https://www.facebook.com/nacsphil/videos/123456789/';
        $watch = 'https://fb.watch/example123/';

        $this->assertSame($good, FacebookMediaUrl::normalize($good));
        $this->assertSame($watch, FacebookMediaUrl::normalize($watch));
        $this->assertNull(FacebookMediaUrl::normalize('http://www.facebook.com/nacsphil/videos/123/'));
        $this->assertNull(FacebookMediaUrl::normalize('https://example.com/video'));
        $this->assertNull(FacebookMediaUrl::normalize('javascript:alert(1)'));

        $embed = FacebookMediaUrl::embedUrl($good);

        $this->assertIsString($embed);
        $this->assertStringStartsWith('https://www.facebook.com/plugins/video.php?href=', $embed);
        $this->assertStringContainsString(rawurlencode($good), $embed);
    }

    public function test_principal_can_publish_a_confirmed_public_facebook_video(): void
    {
        $principal = User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
            'role' => 'principal',
        ]);

        $this->actingAs($principal)->post(route('admin.facebook-media.store'), [
            'title' => 'School Livestream',
            'media_type' => 'live',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
            'status' => 'published',
            'is_featured' => '1',
            'public_confirmed' => '1',
        ])->assertRedirect(route('admin.facebook-media.index'));

        $this->assertDatabaseHas('facebook_media_items', [
            'title' => 'School Livestream',
            'status' => 'published',
            'is_featured' => 1,
        ]);
    }

    public function test_publishing_requires_public_confirmation_and_rejects_non_facebook_urls(): void
    {
        $principal = User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
            'role' => 'principal',
        ]);

        $this->actingAs($principal)
            ->from(route('admin.facebook-media.create'))
            ->post(route('admin.facebook-media.store'), [
                'title' => 'Invalid Host',
                'media_type' => 'video',
                'facebook_url' => 'https://example.com/video',
                'status' => 'published',
                'public_confirmed' => '1',
            ])
            ->assertSessionHasErrors('facebook_url');

        $this->actingAs($principal)
            ->from(route('admin.facebook-media.create'))
            ->post(route('admin.facebook-media.store'), [
                'title' => 'Missing Confirmation',
                'media_type' => 'video',
                'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
                'status' => 'published',
            ])
            ->assertSessionHasErrors('public_confirmed');
    }

    public function test_teacher_entries_are_forced_to_draft(): void
    {
        $teacher = User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
            'role' => 'teacher',
        ]);

        $this->actingAs($teacher)->post(route('admin.facebook-media.store'), [
            'title' => 'Teacher Submission',
            'media_type' => 'video',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
            'status' => 'published',
            'is_featured' => '1',
            'public_confirmed' => '1',
        ])->assertRedirect(route('admin.facebook-media.index'));

        $this->assertDatabaseHas('facebook_media_items', [
            'title' => 'Teacher Submission',
            'status' => 'draft',
            'is_featured' => 0,
            'published_at' => null,
        ]);
    }

    public function test_admin_form_generates_facebook_preview_from_pasted_link(): void
    {
        $form = file_get_contents(resource_path('views/admin/facebook-media/form.blade.php'));
        $script = file_get_contents(public_path('assets/phase22-media/media.js'));
        $adminLayout = file_get_contents(resource_path('views/admin/layouts/app.blade.php'));

        $this->assertIsString($form);
        $this->assertIsString($script);
        $this->assertIsString($adminLayout);

        $this->assertStringContainsString('data-facebook-url-input', $form);
        $this->assertStringContainsString('data-facebook-admin-preview', $form);
        $this->assertStringContainsString('data-facebook-preview-frame', $form);
        $this->assertStringContainsString("document.createElement('iframe')", $script);
        $this->assertStringContainsString('new URLSearchParams', $script);
        $this->assertStringContainsString("host.endsWith('.facebook.com')", $script);
        $this->assertStringContainsString('assets/phase22-media/media.js', $adminLayout);
    }

    public function test_public_navigation_and_sitemap_include_live_and_videos_once_per_navigation_surface(): void
    {
        $header = file_get_contents(resource_path('views/partials/public-header.blade.php'));
        $footer = file_get_contents(resource_path('views/partials/public-footer.blade.php'));
        $sitemap = file_get_contents(app_path('Http/Controllers/SitemapController.php'));

        $this->assertIsString($header);
        $this->assertIsString($footer);
        $this->assertIsString($sitemap);

        $this->assertSame(2, substr_count($header, "route('media.index')"));
        $this->assertSame(2, substr_count($header, 'Live &amp; Videos'));
        $this->assertSame(1, substr_count($footer, "route('media.index')"));
        $this->assertSame(1, substr_count($sitemap, "route('media.index')"));
    }

    public function test_media_items_use_safe_trash_and_audit_trait(): void
    {
        $model = file_get_contents(app_path('Models/FacebookMediaItem.php'));
        $trash = file_get_contents(app_path('Http/Controllers/Admin/TrashController.php'));

        $this->assertStringContainsString('SoftDeletes', $model);
        $this->assertStringContainsString('HasContentAudit', $model);
        $this->assertStringContainsString('FacebookMediaItem::onlyTrashed()', $trash);
        $this->assertStringContainsString("'media' => FacebookMediaItem::class", $trash);
    }
}
