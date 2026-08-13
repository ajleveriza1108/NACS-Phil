<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEntry;
use App\Models\AdmissionApplication;
use App\Models\Announcement;
use App\Models\FacultyProfile;
use App\Models\Inquiry;
use App\Models\SchoolDocument;
use App\Models\SchoolSetting;
use App\Models\SeoSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase12SchoolBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_school_resource_pages_are_available(): void
    {
        $this->get('/faculty')->assertOk()->assertSee('Faculty &amp; Staff', false)->assertSee('assets/phase12-school/backend-public.css', false);
        $this->get('/documents')->assertOk()->assertSee('Documents &amp; Downloads', false);
        $this->get('/calendar')->assertOk()->assertSee('Academic Calendar');
    }

    public function test_sitemap_and_robots_are_available(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->get('/robots.txt')->assertOk()->assertSee('/admin');
    }

    public function test_only_published_faculty_profiles_are_public(): void
    {
        FacultyProfile::create([
            'name' => 'Published Teacher',
            'position' => 'Teacher',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        FacultyProfile::create([
            'name' => 'Draft Teacher',
            'position' => 'Teacher',
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $this->get('/faculty')
            ->assertOk()
            ->assertSee('Published Teacher')
            ->assertDontSee('Draft Teacher');
    }

    public function test_public_document_download_is_limited_to_public_published_documents(): void
    {
        $root = storage_path('app/private/documents');
        if (! is_dir($root)) {
            mkdir($root, 0750, true);
        }

        file_put_contents($root.'/public-test.pdf', '%PDF-1.4 test');

        $public = SchoolDocument::create([
            'title' => 'Public Handbook',
            'description' => 'Approved resource',
            'category' => 'Handbook',
            'file_path' => 'public-test.pdf',
            'original_name' => 'handbook.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 20,
            'audience' => 'public',
            'published_at' => now(),
            'sort_order' => 0,
        ]);

        $private = SchoolDocument::create([
            'title' => 'Staff Only',
            'description' => 'Private resource',
            'category' => 'Staff',
            'file_path' => 'staff.pdf',
            'original_name' => 'staff.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 20,
            'audience' => 'staff',
            'published_at' => now(),
            'sort_order' => 0,
        ]);

        $this->get('/documents')->assertSee('Public Handbook')->assertDontSee('Staff Only');
        $this->get(route('documents.download', $public))->assertOk();
        $this->get(route('documents.download', $private))->assertNotFound();
    }

    public function test_academic_calendar_only_shows_published_entries(): void
    {
        AcademicCalendarEntry::create([
            'title' => 'First Day of Classes',
            'category' => 'academic',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'is_published' => true,
        ]);

        AcademicCalendarEntry::create([
            'title' => 'Internal Draft Date',
            'category' => 'academic',
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(2)->addHour(),
            'is_published' => false,
        ]);

        $this->get('/calendar')
            ->assertSee('First Day of Classes')
            ->assertDontSee('Internal Draft Date');
    }

    public function test_teacher_submits_announcement_for_principal_review_before_publication(): void
    {
        $teacher = $this->staff('teacher');
        $principal = $this->staff('principal');

        $this->actingAs($teacher)->post(route('admin.announcements.store'), [
            'title' => 'Teacher Draft Notice',
            'excerpt' => 'For review',
            'body' => 'This announcement needs leadership review.',
            'type' => 'info',
            'audience' => 'public',
            'sort_order' => 0,
            'action' => 'submit_review',
        ])->assertRedirect(route('admin.announcements.index'));

        $announcement = Announcement::where('title', 'Teacher Draft Notice')->firstOrFail();

        $this->assertSame('pending_review', $announcement->workflow_status);
        $this->assertNull($announcement->published_at);
        $this->get('/announcements')->assertDontSee('Teacher Draft Notice');

        $this->actingAs($principal)->patch(route('admin.reviews.decide', ['announcement', $announcement->id]), [
            'decision' => 'approve',
            'review_notes' => 'Approved.',
        ])->assertRedirect();

        $announcement->refresh();
        $this->assertSame('published', $announcement->workflow_status);
        $this->assertNotNull($announcement->published_at);
        $this->get('/announcements')->assertSee('Teacher Draft Notice');
    }

    public function test_inquiry_crm_fields_can_be_managed_by_principal(): void
    {
        $principal = $this->staff('principal');
        $teacher = $this->staff('teacher');

        $inquiry = Inquiry::create([
            'guardian_name' => 'Parent Example',
            'email' => 'parent@example.test',
            'level_interested' => 'Grade 1',
            'message' => 'Interested in Grade 1',
            'status' => 'new',
            'privacy_consent_at' => now(),
        ]);

        $this->actingAs($principal)->patch(route('admin.inquiries.update', $inquiry), [
            'status' => 'follow_up',
            'assigned_to_user_id' => $teacher->id,
            'follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'source' => 'Website',
            'interest_level' => 'high',
            'admin_notes' => 'Call tomorrow.',
        ])->assertRedirect();

        $inquiry->refresh();
        $this->assertSame('follow_up', $inquiry->status);
        $this->assertSame($teacher->id, $inquiry->assigned_to_user_id);
        $this->assertSame('high', $inquiry->interest_level);
    }

    public function test_admissions_checklist_is_created_for_review(): void
    {
        $principal = $this->staff('principal');

        $application = AdmissionApplication::create([
            'reference_code' => AdmissionApplication::createReferenceCode(),
            'access_code_hash' => Hash::make('ABCD1234EFGH'),
            'guardian_name' => 'Guardian',
            'guardian_email' => 'guardian@example.test',
            'student_name' => 'Learner',
            'applying_for_level' => 'Grade 1',
            'school_year' => '2026-2027',
            'status' => 'submitted',
            'privacy_consent_at' => now(),
            'application_consent_at' => now(),
            'submitted_at' => now(),
        ]);

        $this->actingAs($principal)
            ->get(route('admin.admissions.show', $application))
            ->assertOk()
            ->assertSee('Application Checklist');

        $this->assertSame(count(AdmissionApplication::DEFAULT_CHECKLIST), $application->checklistItems()->count());
    }

    public function test_school_settings_and_seo_are_principal_managed(): void
    {
        $principal = $this->staff('principal');
        $teacher = $this->staff('teacher');

        $this->actingAs($teacher)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.seo.edit'))->assertForbidden();

        $this->actingAs($principal)->patch(route('admin.settings.update'), [
            'school_name' => 'NACS-Phil',
            'short_name' => 'NACS-Phil',
            'tagline' => 'Faith. Character. Excellence.',
            'current_school_year' => '2026-2027',
            'address' => 'Sariaya, Quezon',
            'phone' => '',
            'email' => '',
            'office_hours' => '',
            'facebook_url' => '',
            'privacy_email' => '',
            'emergency_banner' => 'Classes suspended tomorrow.',
        ])->assertRedirect();

        $this->assertSame('Classes suspended tomorrow.', SchoolSetting::valueFor('emergency_banner'));
        $this->get('/')->assertSee('Classes suspended tomorrow.');

        $this->actingAs($principal)->patch(route('admin.seo.update'), [
            'pages' => [
                'home' => [
                    'title' => 'NACS-Phil Christian School',
                    'meta_description' => 'Official NACS-Phil school website.',
                    'no_index' => 0,
                ],
            ],
        ])->assertRedirect();

        $this->assertSame('NACS-Phil Christian School', SeoSetting::where('page_key', 'home')->value('title'));
    }

    public function test_staff_can_begin_local_two_factor_setup_without_external_service(): void
    {
        $principal = $this->staff('principal');

        $this->actingAs($principal)
            ->post(route('admin.security.two-factor.setup'), ['current_password' => 'password'])
            ->assertRedirect()
            ->assertSessionHas('two_factor_setup_secret');

        $secret = session('two_factor_setup_secret');
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_system_health_is_super_admin_only_and_security_page_is_available_to_staff(): void
    {
        $teacher = $this->staff('teacher');
        $super = User::factory()->create([
            'is_admin' => true,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->actingAs($teacher)->get(route('admin.security.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.system-health'))->assertForbidden();
        $this->actingAs($super)->get(route('admin.system-health'))->assertOk()->assertSee('System Health');
    }

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
