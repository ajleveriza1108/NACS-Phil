<?php

namespace Tests\Feature;

use Tests\TestCase;

class Phase21NewsEventsGalleryFidelityTest extends TestCase
{
    public function test_news_events_and_gallery_load_phase_twenty_one_after_consistency_layer(): void
    {
        foreach (['news-phase5.blade.php', 'events-phase6.blade.php', 'gallery-phase7.blade.php'] as $layout) {
            $source = file_get_contents(resource_path('views/layouts/'.$layout));

            $this->assertIsString($source, $layout);

            $phase18 = strpos($source, 'assets/phase18-consistency/site-consistency.css');
            $phase21 = strpos($source, 'assets/phase21-news-events-gallery/fidelity.css');

            $this->assertNotFalse($phase18, $layout);
            $this->assertNotFalse($phase21, $layout);
            $this->assertGreaterThan($phase18, $phase21, $layout);
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

    public function test_phase_twenty_one_css_protects_dark_detail_and_privacy_contrast(): void
    {
        $css = file_get_contents(public_path('assets/phase21-news-events-gallery/fidelity.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.news-phase5 .news-detail-hero h1', $css);
        $this->assertStringContainsString('.events-phase6 .event-detail-hero h1', $css);
        $this->assertStringContainsString('.gallery-phase7 .g-privacy h2', $css);
        $this->assertStringContainsString('color:#fff!important', $css);
    }

    public function test_phase_twenty_one_css_covers_desktop_tablet_phone_narrow_and_reduced_motion(): void
    {
        $css = file_get_contents(public_path('assets/phase21-news-events-gallery/fidelity.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media(max-width:1180px)', $css);
        $this->assertStringContainsString('@media(max-width:960px)', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
        $this->assertStringContainsString('@media(max-width:480px)', $css);
        $this->assertStringContainsString('@media(max-width:380px)', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
    }
}
