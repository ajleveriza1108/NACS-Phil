<?php

namespace App\Support;

class NewsContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'School News & Announcements',
            'hero_heading' => 'Stay connected with',
            'hero_highlight' => 'what is happening at NACS-Phil.',
            'hero_lead' => 'Read school announcements, important notices, community updates, and stories published by authorized NACS-Phil staff.',
            'listing_heading' => 'Latest from the school community',
            'listing_text' => 'Published announcements appear here automatically. Teachers continue using the existing Announcements area to create and publish posts.',
            'empty_heading' => 'No published announcements yet.',
            'empty_text' => 'When authorized staff publish the first announcement, it will appear here automatically.',
            'detail_back_label' => 'Back to News',
            'detail_footer_heading' => 'Need more information?',
            'detail_footer_text' => 'Contact the school if you need clarification about an announcement or official school notice.',
            'detail_contact_button' => 'Contact the School',
        ];
    }
}