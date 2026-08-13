<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_style_admin_without_explicit_role_falls_back_to_super_admin(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($admin->isSuperAdmin());

        $this->actingAs($admin)
            ->get('/admin/staff')
            ->assertOk();
    }

    public function test_super_admin_can_create_teacher_account(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post('/admin/staff', [
                'name' => 'Sample Teacher',
                'email' => 'teacher@example.test',
                'role' => 'teacher',
                'password' => 'StrongTeacher123',
                'password_confirmation' => 'StrongTeacher123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.test',
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => true,
        ]);
    }

    public function test_principal_can_manage_website_settings_but_not_staff_accounts(): void
    {
        $principal = User::factory()->create([
            'is_admin' => true,
            'role' => 'principal',
            'is_active' => true,
        ]);

        $this->actingAs($principal)
            ->get('/admin/contact-content')
            ->assertOk();

        $this->actingAs($principal)
            ->get('/admin/staff')
            ->assertForbidden();
    }

    public function test_teacher_can_use_daily_posting_but_not_school_settings_or_inquiries(): void
    {
        $teacher = User::factory()->create([
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->actingAs($teacher)->get('/admin')->assertOk();
        $this->actingAs($teacher)->get('/admin/announcements')->assertOk();
        $this->actingAs($teacher)->get('/admin/events')->assertOk();
        $this->actingAs($teacher)->get('/admin/gallery')->assertOk();
        $this->actingAs($teacher)->get('/admin/contact-content')->assertForbidden();
        $this->actingAs($teacher)->get('/admin/inquiries')->assertForbidden();
    }

    public function test_inactive_staff_account_cannot_open_admin_area(): void
    {
        $teacher = User::factory()->create([
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $this->actingAs($teacher)
            ->get('/admin')
            ->assertForbidden();
    }
}