<?php

namespace Tests\Feature;

use App\Models\AdmissionApplication;
use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase10OfflineReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_public_pages_are_available_without_external_api_calls(): void
    {
        foreach ([
            '/',
            '/about',
            '/programs',
            '/admissions',
            '/admissions/apply',
            '/admissions/track',
            '/announcements',
            '/events',
            '/gallery',
            '/contact',
            '/privacy',
            '/up',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_admin_login_page_is_available_and_admin_area_is_private(): void
    {
        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_teacher_cannot_access_sensitive_administration_tools(): void
    {
        $teacher = User::factory()->create([
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => true,
        ]);

        foreach ([
            '/admin/inquiries',
            '/admin/staff',
            '/admin/trash',
            '/admin/audit',
            '/admin/admissions',
            '/admin/contact-content',
        ] as $path) {
            $this->actingAs($teacher)->get($path)->assertForbidden();
        }
    }

    public function test_principal_can_access_school_management_but_not_staff_accounts(): void
    {
        $principal = User::factory()->create([
            'is_admin' => true,
            'role' => 'principal',
            'is_active' => true,
        ]);

        $this->actingAs($principal)->get('/admin/inquiries')->assertOk();
        $this->actingAs($principal)->get('/admin/trash')->assertOk();
        $this->actingAs($principal)->get('/admin/audit')->assertOk();
        $this->actingAs($principal)->get('/admin/admissions')->assertOk();
        $this->actingAs($principal)->get('/admin/staff')->assertForbidden();
    }

    public function test_admissions_access_code_is_stored_hashed(): void
    {
        $plain = 'ABCD-EFGH-JKLM';

        $application = AdmissionApplication::create([
            'reference_code' => 'NACS-2026-QAREADY1',
            'access_code_hash' => Hash::make(AdmissionApplication::normalizeAccessCode($plain)),
            'guardian_name' => 'QA Parent',
            'guardian_email' => 'qa@example.test',
            'student_name' => 'QA Learner',
            'applying_for_level' => 'Grade 4',
            'school_year' => '2026-2027',
            'status' => 'submitted',
            'privacy_consent_at' => now(),
            'application_consent_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->assertNotSame(
            AdmissionApplication::normalizeAccessCode($plain),
            $application->access_code_hash
        );

        $this->assertTrue($application->verifyAccessCode($plain));
    }

    public function test_gallery_public_scope_still_requires_consent(): void
    {
        GalleryItem::create([
            'title' => 'Private QA Photo',
            'category' => 'QA',
            'image_path' => 'gallery/private-qa.jpg',
            'alt_text' => 'Private QA image',
            'is_published' => true,
            'sort_order' => 0,
            'consent_confirmed_at' => null,
        ]);

        $this->get('/gallery')
            ->assertOk()
            ->assertDontSee('Private QA Photo');
    }
}