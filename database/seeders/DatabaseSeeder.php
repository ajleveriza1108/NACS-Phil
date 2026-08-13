<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\SchoolEvent;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::firstOrCreate(
            ['slug' => 'welcome-to-the-nacs-phil-website-foundation'],
            [
                'title' => 'Welcome to the NACS-Phil Website Foundation',
                'excerpt' => 'This local website is ready for official school content, approved photographs, and current admissions information.',
                'body' => "The NACS-Phil website foundation is now running locally. School administrators should replace development text with approved information before public launch.\n\nNo student records or private family information should be entered into public website content.",
                'type' => 'info',
                'published_at' => now(),
                'is_featured' => true,
                'sort_order' => 0,
            ]
        );

        SchoolEvent::firstOrCreate(
            ['slug' => 'official-calendar-details-pending'],
            [
                'title' => 'Official School Calendar Details Pending',
                'description' => 'Add verified enrollment dates, orientations, examinations, and school activities through the administrator dashboard.',
                'venue' => 'NACS-Phil',
                'starts_at' => now()->addDays(14)->startOfDay()->addHours(8),
                'ends_at' => now()->addDays(14)->startOfDay()->addHours(12),
                'published_at' => now(),
            ]
        );
    }
}
