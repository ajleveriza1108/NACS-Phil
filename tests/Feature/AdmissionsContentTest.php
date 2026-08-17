<?php

namespace Tests\Feature;

use App\Models\SiteContent;
use App\Models\User;
use App\Support\AdmissionsContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionsContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admissions_page_uses_phase_four_responsive_design(): void
    {
        $this->get('/admissions')
            ->assertOk()
            ->assertSee('assets/current/')
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('Admissions')
            ->assertSee('Four steps families can follow with confidence.');
    }

    public function test_admin_can_open_admissions_content_editor(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/admissions-content')
            ->assertOk()
            ->assertSee('Edit Admissions Page')
            ->assertSee('Admissions Steps');
    }

    public function test_admin_can_update_admissions_page_words(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $content = AdmissionsContent::defaults();
        $content['hero_heading'] = 'Your Enrollment Journey';
        $content['hero_highlight'] = 'Starts Here';

        $this->actingAs($admin)
            ->patch('/admin/admissions-content', $content)
            ->assertSessionHasNoErrors();

        $this->assertSame(
            'Your Enrollment Journey',
            SiteContent::query()->where('page', 'admissions')->where('key', 'hero_heading')->value('value')
        );

        $this->get('/admissions')
            ->assertOk()
            ->assertSee('Your Enrollment Journey')
            ->assertSee('Starts Here');
    }

    public function test_non_admin_cannot_edit_admissions_page(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/admissions-content')
            ->assertForbidden();
    }
}
