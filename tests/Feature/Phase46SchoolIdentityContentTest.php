<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase46SchoolIdentityContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_has_history_and_nacs_phil_distinctives(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Our History')
            ->assertSee('NACS-Phil Distinctives')
            ->assertSee('Bible Memory & Recitation')
            ->assertSee('Respect for Elders (Mano Po)');
    }

    public function test_about_editor_renders_all_four_distinctives(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.about-content.edit'))
            ->assertOk()
            ->assertSee('NACS-Phil Distinctives')
            ->assertSee('name="distinctive_1_title"', false)
            ->assertSee('name="distinctive_2_text"', false)
            ->assertSee('name="distinctive_3_title"', false)
            ->assertSee('name="distinctive_4_text"', false);
    }
}
