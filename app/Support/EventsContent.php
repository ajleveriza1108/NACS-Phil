<?php

namespace App\Support;

class EventsContent
{
    public static function defaults(): array
    {
        return [
            'hero_badge' => 'School Events',
            'hero_heading' => 'Moments that bring our',
            'hero_highlight' => 'school community together.',
            'hero_lead' => 'Discover upcoming activities, school programs, special gatherings, and community events published by authorized NACS-Phil staff.',
            'listing_heading' => 'Upcoming events',
            'listing_text' => 'Published events appear automatically in chronological order. Teachers continue using the existing Events manager to create and update event records.',
            'empty_heading' => 'No upcoming public events yet.',
            'empty_text' => 'When the school publishes an upcoming event, it will appear here automatically.',
            'detail_back_label' => 'Back to Events',
            'detail_contact_heading' => 'Have a question about this event?',
            'detail_contact_text' => 'Contact the school for official event guidance, schedule clarifications, or attendance information.',
            'detail_contact_button' => 'Contact the School',
        ];
    }
}