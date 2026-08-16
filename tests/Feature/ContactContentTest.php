<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ContactContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_uses_phase_eight_design(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('assets/current/')
            ->assertSee('viewport-fit=cover', false)
            ->assertSee('Contact NACS-Phil')
            ->assertSee('How may the school assist you?');
    }

    public function test_contact_page_keeps_existing_inquiry_fields_and_privacy_controls(): void
    {
        $this->get('/contact')
            ->assertOk()
            ->assertSee('name="guardian_name"', false)
            ->assertSee('name="privacy_consent"', false)
            ->assertSee('name="website"', false)
            ->assertSee('Email or phone required');
    }

    public function test_admin_can_open_contact_page_settings(): void
    {
        $admin=User::factory()->create(['is_admin'=>true]);

        $this->actingAs($admin)
            ->get('/admin/contact-content')
            ->assertOk()
            ->assertSee('Edit Contact Page')
            ->assertSee('Open Inquiries');
    }

    public function test_admin_can_update_contact_page_settings(): void
    {
        $admin=User::factory()->create(['is_admin'=>true]);
        $content=ContactContent::defaults();
        $content['hero_heading']='Reach Our School Office';
        $content['address']='Verified School Address';

        $this->actingAs($admin)
            ->patch('/admin/contact-content',$content)
            ->assertSessionHasNoErrors();

        $this->get('/contact')
            ->assertSee('Reach Our School Office')
            ->assertSee('Verified School Address');
    }

    public function test_non_admin_cannot_edit_contact_page_settings(): void
    {
        $user=User::factory()->create(['is_admin'=>false]);

        $this->actingAs($user)
            ->get('/admin/contact-content')
            ->assertForbidden();
    }
}
