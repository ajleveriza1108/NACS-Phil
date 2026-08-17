<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Support\ProgramsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_programs_page_uses_phase_three_responsive_design(): void
    {
        $this->get('/programs')
            ->assertOk()
            ->assertSee('assets/current/')
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('Academic Programs')
            ->assertSee('A continuous learning journey for every stage.');
    }

    public function test_admin_can_open_programs_content_editor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/programs-content')
            ->assertOk()
            ->assertSee('Edit Programs Page')
            ->assertSee('Preschool / Early Years');
    }

    public function test_admin_can_update_programs_page_words(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $content = ProgramsContent::defaults();
        $content['hero_heading'] = 'Learning at Every Stage';
        $content['hero_highlight'] = 'Growing with Purpose';

        $this->actingAs($admin)
            ->patch('/admin/programs-content', $content)
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'Learning at Every Stage',
            SiteContent::query()->where('page', 'programs')->where('key', 'hero_heading')->value('value')
        );

        $this->get('/programs')
            ->assertOk()
            ->assertSee('Learning at Every Stage')
            ->assertSee('Growing with Purpose');
    }

    public function test_non_admin_cannot_edit_programs_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/programs-content')
            ->assertForbidden();
    }
}
