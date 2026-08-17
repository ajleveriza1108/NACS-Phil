<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEntry;
use App\Models\AdmissionApplication;
use App\Models\Announcement;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase13AdminExperienceTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_super_admin_dashboard_uses_phase_thirteen_school_manager_shell(): void
    {
        $admin = $this->staff('super_admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('NACS-Phil School Manager')
            ->assertSee('Find a tool...')
            ->assertSee('What needs attention')
            ->assertSee('assets/current/', false)
            ->assertSee('assets/current/', false);
    }

    public function test_teacher_dashboard_keeps_sensitive_school_office_tools_hidden(): void
    {
        $teacher = $this->staff('teacher');

        $this->actingAs($teacher)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Start common work')
            ->assertSee('Your publishing workflow')
            ->assertDontSee('What needs attention')
            ->assertDontSee('Inquiry CRM')
            ->assertDontSee('Applications');
    }

    public function test_principal_dashboard_surfaces_due_followups_reviews_and_admissions(): void
    {
        $principal = $this->staff('principal');

        Inquiry::create([
            'guardian_name' => 'Parent Example',
            'email' => 'parent@example.test',
            'level_interested' => 'Grade 1',
            'message' => 'Please call us.',
            'status' => 'follow_up',
            'privacy_consent_at' => now(),
            'follow_up_at' => now()->subMinute(),
        ]);

        Announcement::create([
            'title' => 'Review Needed',
            'body' => 'Teacher draft for review.',
            'type' => 'info',
            'audience' => 'public',
            'sort_order' => 0,
            'workflow_status' => 'pending_review',
            'submitted_for_review_at' => now(),
        ]);

        AdmissionApplication::create([
            'reference_code' => AdmissionApplication::createReferenceCode(),
            'access_code_hash' => bcrypt('ABCD1234EFGH'),
            'guardian_name' => 'Guardian Example',
            'guardian_email' => 'guardian@example.test',
            'student_name' => 'Learner Example',
            'applying_for_level' => 'Grade 1',
            'school_year' => '2026-2027',
            'status' => 'submitted',
            'privacy_consent_at' => now(),
            'application_consent_at' => now(),
            'submitted_at' => now(),
        ]);

        AcademicCalendarEntry::create([
            'title' => 'First Day of Classes',
            'category' => 'academic',
            'starts_at' => now()->addDay()->startOfDay(),
            'ends_at' => now()->addDay()->endOfDay(),
            'school_year' => '2026-2027',
            'is_all_day' => true,
            'is_published' => true,
            'created_by_user_id' => $principal->id,
        ]);

        $this->actingAs($principal)
            ->get('/admin')
            ->assertOk()
            ->assertSee('What needs attention')
            ->assertSee('Content reviews')
            ->assertSee('Admissions waiting review')
            ->assertSee('Inquiry follow-ups due')
            ->assertSee('First Day of Classes');
    }

    public function test_phase_twelve_role_boundaries_remain_intact(): void
    {
        $teacher = $this->staff('teacher');
        $principal = $this->staff('principal');
        $super = $this->staff('super_admin');

        foreach ([
            '/admin/reviews',
            '/admin/admissions',
            '/admin/inquiries',
            '/admin/faculty',
            '/admin/documents',
            '/admin/calendar',
            '/admin/school-settings',
            '/admin/seo',
        ] as $path) {
            $this->actingAs($teacher)->get($path)->assertForbidden();
            $this->actingAs($principal)->get($path)->assertOk();
        }

        $this->actingAs($principal)->get('/admin/staff')->assertForbidden();
        $this->actingAs($principal)->get('/admin/system-health')->assertForbidden();
        $this->actingAs($super)->get('/admin/staff')->assertOk();
        $this->actingAs($super)->get('/admin/system-health')->assertOk();
    }

    public function test_teacher_content_still_requires_principal_approval_before_publication(): void
    {
        $teacher = $this->staff('teacher');
        $principal = $this->staff('principal');

        $this->actingAs($teacher)
            ->post('/admin/announcements', [
                'title' => 'Teacher Review Test',
                'excerpt' => 'Waiting for school approval.',
                'body' => 'This should not appear publicly before approval.',
                'type' => 'info',
                'audience' => 'public',
                'sort_order' => 0,
                'action' => 'submit_review',
            ])
            ->assertSessionHasNoErrors();

        $announcement = Announcement::where('title', 'Teacher Review Test')->firstOrFail();

        $this->assertSame('pending_review', $announcement->workflow_status);
        $this->assertNull($announcement->published_at);

        $this->get('/announcements/'.$announcement->slug)->assertNotFound();

        $this->actingAs($principal)
            ->patch('/admin/reviews/announcement/'.$announcement->id, [
                'decision' => 'approve',
                'review_notes' => 'Approved for the school website.',
            ])
            ->assertSessionHasNoErrors();

        $announcement->refresh();

        $this->assertSame('published', $announcement->workflow_status);
        $this->assertNotNull($announcement->published_at);

        $this->get('/announcements/'.$announcement->slug)
            ->assertOk()
            ->assertSee('Teacher Review Test');
    }

    public function test_phase_thirteen_responsive_contract_exists(): void
    {
        $css = file_get_contents(public_path('assets/phase13-admin/admin.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media (max-width: 1100px)', $css);
        $this->assertStringContainsString('@media (max-width: 620px)', $css);
        $this->assertStringContainsString('@media (max-width: 420px)', $css);
        $this->assertStringContainsString('min-height: 44px', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('.p13-table-scroll', $css);
    }
}
