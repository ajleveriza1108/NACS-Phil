<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RegistrationInvitationNotification;
use App\Support\StaffAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase46StaffAccessArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_staffing_plan_requires_two_super_admins_and_seven_specialized_editors(): void
    {
        $plan = StaffAccess::staffingPlan();

        $this->assertSame(2, $plan['super_admin']['target']);

        $editors = array_filter(
            array_keys($plan),
            fn (string $role): bool => str_ends_with($role, '_editor')
        );

        $this->assertCount(7, $editors);
        $this->assertSame([
            'frontend_editor',
            'text_editor',
            'news_editor',
            'events_editor',
            'media_editor',
            'admissions_editor',
            'documents_editor',
        ], array_values($editors));
    }

    public function test_specialized_editors_are_restricted_to_their_assigned_admin_areas(): void
    {
        $frontend = $this->staff('frontend_editor');
        $this->actingAs($frontend)->get(route('admin.website-content.edit'))->assertOk();
        $this->actingAs($frontend)->get(route('admin.branding.edit'))->assertOk();
        $this->actingAs($frontend)->get(route('admin.admissions.index'))->assertForbidden();
        $this->actingAs($frontend)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($frontend)->get(route('admin.students.index'))->assertForbidden();

        $text = $this->staff('text_editor');
        $this->actingAs($text)->get(route('admin.about-content.edit'))->assertOk();
        $this->actingAs($text)->get(route('admin.branding.edit'))->assertForbidden();
        $this->actingAs($text)->get(route('admin.announcements.index'))->assertForbidden();

        $news = $this->staff('news_editor');
        $this->actingAs($news)->get(route('admin.announcements.index'))->assertOk();
        $this->actingAs($news)->get(route('admin.news-content.edit'))->assertOk();
        $this->actingAs($news)->get(route('admin.events.index'))->assertForbidden();

        $events = $this->staff('events_editor');
        $this->actingAs($events)->get(route('admin.events.index'))->assertOk();
        $this->actingAs($events)->get(route('admin.calendar.index'))->assertOk();
        $this->actingAs($events)->get(route('admin.announcements.index'))->assertForbidden();

        $media = $this->staff('media_editor');
        $this->actingAs($media)->get(route('admin.gallery.index'))->assertOk();
        $this->actingAs($media)->get(route('admin.media.index'))->assertOk();
        $this->actingAs($media)->get(route('admin.admissions.index'))->assertForbidden();

        $admissions = $this->staff('admissions_editor');
        $this->actingAs($admissions)->get(route('admin.admissions.index'))->assertOk();
        $this->actingAs($admissions)->get(route('admin.inquiries.index'))->assertOk();
        $this->actingAs($admissions)->get(route('admin.students.index'))->assertForbidden();

        $documents = $this->staff('documents_editor');
        $this->actingAs($documents)->get(route('admin.documents.index'))->assertOk();
        $this->actingAs($documents)->get(route('admin.website-content.edit'))->assertForbidden();
    }

    public function test_teacher_keeps_existing_student_and_reviewed_daily_content_access(): void
    {
        $teacher = $this->staff('teacher');

        $this->actingAs($teacher)->get(route('admin.students.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.announcements.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.events.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.gallery.index'))->assertOk();
        $this->actingAs($teacher)->get(route('admin.admissions.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.staff.index'))->assertForbidden();
    }

    public function test_super_admin_can_securely_invite_a_second_super_admin(): void
    {
        Notification::fake();

        $admin = $this->staff('super_admin');

        $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'Second Administrator',
                'email' => 'second.admin@example.test',
                'role' => 'super_admin',
            ])
            ->assertRedirect(route('admin.staff.index'))
            ->assertSessionHasNoErrors();

        $second = User::query()->where('email', 'second.admin@example.test')->firstOrFail();

        $this->assertTrue($second->is_admin);
        $this->assertTrue($second->isSuperAdmin());
        $this->assertFalse($second->is_active);
        $this->assertNull($second->email_verified_at);

        Notification::assertSentTo($second, RegistrationInvitationNotification::class);
    }

    public function test_official_staff_email_domain_is_enforced_only_after_configuration(): void
    {
        Notification::fake();

        $admin = $this->staff('super_admin');

        config(['nacs.school_email_domain' => 'nacsphil.edu.ph']);

        $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'News Editor',
                'email' => 'editor@gmail.com',
                'role' => 'news_editor',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'editor@gmail.com']);

        $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'News Editor',
                'email' => 'NEWS.EDITOR@NACSPHIL.EDU.PH',
                'role' => 'news_editor',
            ])
            ->assertRedirect(route('admin.staff.index'))
            ->assertSessionHasNoErrors();

        $editor = User::query()->where('email', 'news.editor@nacsphil.edu.ph')->firstOrFail();

        $this->assertSame('news_editor', $editor->role);
        $this->assertFalse($editor->is_active);
        Notification::assertSentTo($editor, RegistrationInvitationNotification::class);
    }

    public function test_staff_accounts_page_exposes_the_access_readiness_plan(): void
    {
        $admin = $this->staff('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.staff.index'))
            ->assertOk()
            ->assertSee('2 administrators + 7 specialized editor seats')
            ->assertSee('Frontend Editor')
            ->assertSee('Text / Content Editor')
            ->assertSee('News / Posting Editor')
            ->assertSee('Events Editor')
            ->assertSee('Media / Gallery Editor')
            ->assertSee('Admissions Editor')
            ->assertSee('Documents / Resources Editor');
    }

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
