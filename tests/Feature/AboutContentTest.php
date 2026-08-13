<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Support\AboutContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_uses_phase_two_responsive_design(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('assets/phase2-about/about.css')
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('About NACS-Phil')
            ->assertSee('Mission and vision presented clearly');
    }

    public function test_admin_can_open_about_content_editor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/about-content')
            ->assertOk()
            ->assertSee('Edit About Page')
            ->assertSee('Mission & Vision', false);
    }

    public function test_admin_can_update_about_page_words(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $content = AboutContent::defaults();
        $content['hero_heading'] = 'Learning with Faith';
        $content['hero_highlight'] = 'Serving with Purpose';

        $this->actingAs($admin)
            ->patch('/admin/about-content', $content)
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'Learning with Faith',
            SiteContent::query()->where('page', 'about')->where('key', 'hero_heading')->value('value')
        );

        $this->get('/about')
            ->assertOk()
            ->assertSee('Learning with Faith')
            ->assertSee('Serving with Purpose');
    }

    public function test_non_admin_cannot_edit_about_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/about-content')
            ->assertForbidden();
    }
}