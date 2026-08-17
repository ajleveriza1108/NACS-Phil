<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Support\NewsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_index_uses_phase_five_responsive_design(): void
    {
        $this->get('/announcements')
            ->assertOk()
            ->assertSee('assets/current/')
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('School News &amp; Announcements', false)
            ->assertSee('Latest from the school community');
    }

    public function test_admin_can_open_news_page_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/news-content')
            ->assertOk()
            ->assertSee('Edit News Page')
            ->assertSee('Open Announcements');
    }

    public function test_admin_can_update_news_page_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $content = NewsContent::defaults();
        $content['hero_heading'] = 'News from Our School';
        $content['hero_highlight'] = 'Stay Connected';

        $this->actingAs($admin)
            ->patch('/admin/news-content', $content)
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'News from Our School',
            SiteContent::query()->where('page', 'news')->where('key', 'hero_heading')->value('value')
        );

        $this->get('/announcements')
            ->assertOk()
            ->assertSee('News from Our School')
            ->assertSee('Stay Connected');
    }

    public function test_non_admin_cannot_edit_news_page_settings(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/news-content')
            ->assertForbidden();
    }
}
