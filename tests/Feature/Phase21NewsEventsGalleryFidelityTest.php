<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase21NewsEventsGalleryFidelityTest extends TestCase
{
    public function test_news_events_and_gallery_use_current_semantic_bundles(): void
    {
        foreach ([
            'announcements/index.blade.php' => 'news',
            'events/index.blade.php' => 'events',
            'gallery/index.blade.php' => 'gallery',
        ] as $view => $bundle) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertIsString($source, $view);
            $this->assertStringContainsString("extends('layouts.site-current'", $source, $view);
            $this->assertStringContainsString("'assetBundle' => '".$bundle."'", $source, $view);
            $this->assertFileExists(public_path('assets/current/pages/'.$bundle.'.css'));
        }
    }

    public function test_news_preserves_cms_featured_pagination_and_detail_routes(): void
    {
        $index = file_get_contents(resource_path('views/announcements/index.blade.php'));
        $show = file_get_contents(resource_path('views/announcements/show.blade.php'));

        $this->assertIsString($index);
        $this->assertIsString($show);
        $this->assertStringContainsString("SiteContent::valuesFor('news'", $index);
        $this->assertStringContainsString('is_featured', $index);
        $this->assertStringContainsString('$announcements->links()', $index);
        $this->assertStringContainsString("route('announcements.show'", $index);
        $this->assertStringContainsString("route('announcements.index')", $show);
        $this->assertStringContainsString('nl2br(e($announcementBody))', $show);
    }

    public function test_events_preserve_dates_venue_registration_detail_and_pagination(): void
    {
        $index = file_get_contents(resource_path('views/events/index.blade.php'));
        $show = file_get_contents(resource_path('views/events/show.blade.php'));

        $this->assertIsString($index);
        $this->assertIsString($show);
        $this->assertStringContainsString("SiteContent::valuesFor('events'", $index);
        $this->assertStringContainsString('$event->starts_at', $index);
        $this->assertStringContainsString('$event->venue', $index);
        $this->assertStringContainsString('$event->registration_url', $index);
        $this->assertStringContainsString("route('events.show'", $index);
        $this->assertStringContainsString('$upcomingEvents->links()', $index);
        $this->assertStringContainsString("route('events.index')", $show);
        $this->assertStringContainsString('rel="noopener noreferrer"', $show);
    }

    public function test_gallery_preserves_approved_storage_filters_lightbox_and_privacy(): void
    {
        $source = file_get_contents(resource_path('views/gallery/index.blade.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString("SiteContent::valuesFor('gallery'", $source);
        $this->assertStringContainsString('Storage::url($item->image_path)', $source);
        $this->assertStringContainsString('$galleryCategories', $source);
        $this->assertStringContainsString('$activeCategory', $source);
        $this->assertStringContainsString('data-g-open', $source);
        $this->assertStringContainsString('data-image=', $source);
        $this->assertStringContainsString('$item->photographer_credit', $source);
        $this->assertStringContainsString("route('privacy')", $source);
        $this->assertStringContainsString('$galleryItems->links()', $source);
    }

    public function test_event_views_have_no_known_mojibake_sequences(): void
    {
        foreach (['events/index.blade.php', 'events/show.blade.php'] as $view) {
            $source = file_get_contents(resource_path('views/'.$view));
            $this->assertIsString($source, $view);
            $this->assertStringNotContainsString('â', $source, $view);
            $this->assertStringNotContainsString('Â', $source, $view);
        }
    }

    public function test_current_news_events_gallery_bundles_keep_dark_contrast_and_responsive_rules(): void
    {
        foreach (['news', 'events', 'gallery'] as $bundle) {
            $css = file_get_contents(public_path('assets/current/pages/'.$bundle.'.css'));
            $this->assertIsString($css);
            $this->assertStringContainsString('color:#fff!important', $css);
            $this->assertStringContainsString('@media(max-width:760px)', $css);
            $this->assertStringContainsString('@media(max-width:380px)', $css);
            $this->assertStringContainsString('prefers-reduced-motion', $css);
        }
    }
}
