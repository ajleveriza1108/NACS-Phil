<?php

namespace Tests\Feature;

use App\Models\SchoolEvent;
use App\Models\User;
use App\Support\EventsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_page_uses_phase_six_design(): void
    {
        $this->get('/events')
            ->assertOk()
            ->assertSee('assets/phase6-events/events.css')
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('School Events')
            ->assertSee('Upcoming events');
    }

    public function test_published_event_has_public_detail_page(): void
    {
        $event = SchoolEvent::create([
            'title' => 'Community Day',
            'description' => 'A school community gathering.',
            'venue' => 'School Campus',
            'starts_at' => now()->addDays(2)->setTime(9,0),
            'ends_at' => now()->addDays(2)->setTime(12,0),
            'is_all_day' => false,
            'published_at' => now(),
        ]);

        $this->get(route('events.show', ['event' => $event->slug]))
            ->assertOk()
            ->assertSee('Community Day')
            ->assertSee('School Campus');
    }

    public function test_unpublished_event_detail_is_not_public(): void
    {
        $event = SchoolEvent::create([
            'title' => 'Private Event',
            'description' => 'Not public.',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'is_all_day' => false,
            'published_at' => null,
        ]);

        $this->get(route('events.show', ['event' => $event->slug]))->assertNotFound();
    }

    public function test_admin_can_open_events_page_settings(): void
    {
        $admin = User::factory()->create(['is_admin'=>true]);
        $this->actingAs($admin)->get('/admin/events-content')
            ->assertOk()
            ->assertSee('Edit Events Page')
            ->assertSee('Open Events Manager');
    }

    public function test_admin_can_update_events_page_settings(): void
    {
        $admin=User::factory()->create(['is_admin'=>true]);
        $content=EventsContent::defaults();
        $content['hero_heading']='Life at NACS-Phil';
        $this->actingAs($admin)->patch('/admin/events-content',$content)->assertSessionHasNoErrors();
        $this->get('/events')->assertSee('Life at NACS-Phil');
    }
}