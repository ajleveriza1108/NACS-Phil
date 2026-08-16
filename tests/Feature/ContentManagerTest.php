<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_teacher_friendly_homepage_editor_with_real_content_keys(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/website-content')
            ->assertOk()
            ->assertSee('Visual Homepage Editor')
            ->assertSee('Hero / First Screen')
            ->assertSee('name="hero_badge"', false)
            ->assertSee('name="hero_heading"', false)
            ->assertSee('name="hero_highlight"', false)
            ->assertSee('name="hero_primary_button"', false)
            ->assertSee('name="contact_email"', false)
            ->assertSee('name="hero_image"', false)
            ->assertSee('name="hero_image_focus_x"', false)
            ->assertSee('name="hero_image_focus_y"', false)
            ->assertSee('name="hero_image_zoom"', false)
            ->assertDontSee('name="0"', false);
    }

    public function test_admin_can_update_homepage_text_without_changing_layout(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $content = \App\Support\HomeContent::defaults();
        $content['hero_heading'] = 'Learning with Purpose';
        $content['hero_highlight'] = 'Growing in Christ';

        $response = $this->actingAs($admin)->patch('/admin/website-content', $content);

        $response->assertSessionHasNoErrors();

        $this->assertSame(
            'Learning with Purpose',
            SiteContent::query()->where('page', 'home')->where('key', 'hero_heading')->value('value')
        );

        $this->get('/')
            ->assertOk()
            ->assertSee('Learning with Purpose')
            ->assertSee('Growing in Christ')
            ->assertSee('Explore Programs');
    }

    public function test_non_admin_cannot_edit_homepage_content(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/website-content')
            ->assertForbidden();
    }
}
