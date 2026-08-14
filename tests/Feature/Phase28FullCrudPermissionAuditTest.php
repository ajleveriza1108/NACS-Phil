<?php

namespace Tests\Feature;

use App\Models\AcademicCalendarEntry;
use App\Models\AdmissionApplication;
use App\Models\AdmissionDocument;
use App\Models\Announcement;
use App\Models\FacebookMediaItem;
use App\Models\FacultyProfile;
use App\Models\GalleryItem;
use App\Models\Inquiry;
use App\Models\MediaAsset;
use App\Models\SchoolDocument;
use App\Models\SchoolEvent;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase28FullCrudPermissionAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_announcement_create_update_delete_and_restore_buttons_execute(): void
    {
        $principal = $this->staff('principal');
        $this->actingAs($principal);

        $this->post(route('admin.announcements.store'), [
            'title' => 'Phase 28 Announcement',
            'excerpt' => 'Created by write-action audit.',
            'body' => 'Announcement body.',
            'type' => 'info',
            'audience' => 'public',
            'sort_order' => 0,
            'is_published' => '1',
        ])->assertRedirect(route('admin.announcements.index'));

        $item = Announcement::where('title', 'Phase 28 Announcement')->firstOrFail();
        $this->assertSame('published', $item->workflow_status);

        $this->patch(route('admin.announcements.update', $item), [
            'title' => 'Phase 28 Announcement Updated',
            'excerpt' => 'Updated by write-action audit.',
            'body' => 'Updated announcement body.',
            'type' => 'urgent',
            'audience' => 'public',
            'sort_order' => 1,
            'is_published' => '1',
        ])->assertRedirect(route('admin.announcements.index'));

        $item->refresh();
        $this->assertSame('Phase 28 Announcement Updated', $item->title);
        $this->assertSame('urgent', $item->type);

        $this->delete(route('admin.announcements.destroy', $item))->assertRedirect();
        $this->assertSoftDeleted('announcements', ['id' => $item->id]);

        $this->patch(route('admin.trash.restore', ['type' => 'announcement', 'id' => $item->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('announcements', ['id' => $item->id, 'deleted_at' => null]);
    }

    public function test_event_create_update_delete_and_restore_buttons_execute(): void
    {
        $principal = $this->staff('principal');
        $this->actingAs($principal);

        $this->post(route('admin.events.store'), [
            'title' => 'Phase 28 Event',
            'description' => 'Event write-action audit.',
            'venue' => 'School Campus',
            'starts_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(5)->addHours(2)->format('Y-m-d H:i:s'),
            'registration_url' => 'https://example.test/register',
            'is_published' => '1',
        ])->assertRedirect(route('admin.events.index'));

        $event = SchoolEvent::where('title', 'Phase 28 Event')->firstOrFail();

        $this->patch(route('admin.events.update', $event), [
            'title' => 'Phase 28 Event Updated',
            'description' => 'Updated event audit.',
            'venue' => 'NACS-Phil Campus',
            'starts_at' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(6)->addHours(2)->format('Y-m-d H:i:s'),
            'registration_url' => '',
            'is_published' => '1',
        ])->assertRedirect(route('admin.events.index'));

        $event->refresh();
        $this->assertSame('Phase 28 Event Updated', $event->title);

        $this->delete(route('admin.events.destroy', $event))->assertRedirect();
        $this->assertSoftDeleted('school_events', ['id' => $event->id]);

        $this->patch(route('admin.trash.restore', ['type' => 'event', 'id' => $event->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('school_events', ['id' => $event->id, 'deleted_at' => null]);
    }

    public function test_gallery_upload_replace_delete_and_restore_keeps_recoverable_file(): void
    {
        Storage::fake('public');

        $principal = $this->staff('principal');
        $this->actingAs($principal);

        $this->post(route('admin.gallery.store'), [
            'title' => 'Phase 28 Photo',
            'category' => 'School Life',
            'image' => $this->pngUpload('phase28-first.png'),
            'alt_text' => 'Phase 28 audit image',
            'caption' => 'Audit photo.',
            'sort_order' => 0,
            'is_published' => '1',
            'consent_confirmed' => '1',
        ])->assertRedirect(route('admin.gallery.index'));

        $photo = GalleryItem::where('title', 'Phase 28 Photo')->firstOrFail();
        $oldPath = $photo->image_path;

        Storage::disk('public')->assertExists($oldPath);

        $this->patch(route('admin.gallery.update', $photo), [
            'title' => 'Phase 28 Photo Updated',
            'category' => 'Activities',
            'image' => $this->pngUpload('phase28-second.png'),
            'alt_text' => 'Updated Phase 28 audit image',
            'caption' => 'Updated audit photo.',
            'sort_order' => 1,
            'is_published' => '1',
            'consent_confirmed' => '1',
        ])->assertRedirect(route('admin.gallery.index'));

        $photo->refresh();
        $newPath = $photo->image_path;

        $this->assertNotSame($oldPath, $newPath);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($newPath);

        $this->delete(route('admin.gallery.destroy', $photo))->assertRedirect();
        $this->assertSoftDeleted('gallery_items', ['id' => $photo->id]);
        Storage::disk('public')->assertExists($newPath);

        $this->patch(route('admin.trash.restore', ['type' => 'photo', 'id' => $photo->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('gallery_items', ['id' => $photo->id, 'deleted_at' => null]);
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_facebook_media_teacher_draft_principal_publish_delete_and_restore_permissions_execute(): void
    {
        $teacher = $this->staff('teacher');

        $this->actingAs($teacher)->post(route('admin.facebook-media.store'), [
            'title' => 'Phase 28 Facebook Video',
            'media_type' => 'video',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
            'description' => 'Teacher submission.',
            'status' => 'published',
            'public_confirmed' => '1',
        ])->assertRedirect(route('admin.facebook-media.index'));

        $item = FacebookMediaItem::where('title', 'Phase 28 Facebook Video')->firstOrFail();

        $this->assertSame('draft', $item->status);
        $this->assertNull($item->published_at);

        $this->actingAs($teacher)
            ->delete(route('admin.facebook-media.destroy', $item))
            ->assertForbidden();

        $principal = $this->staff('principal');

        $this->actingAs($principal)->patch(route('admin.facebook-media.update', $item), [
            'title' => 'Phase 28 Facebook Video Published',
            'media_type' => 'video',
            'facebook_url' => 'https://www.facebook.com/nacsphil/videos/123456789/',
            'description' => 'Approved by leadership.',
            'status' => 'published',
            'public_confirmed' => '1',
            'is_featured' => '1',
        ])->assertRedirect(route('admin.facebook-media.index'));

        $item->refresh();

        $this->assertSame('published', $item->status);
        $this->assertNotNull($item->published_at);
        $this->assertNotNull($item->public_confirmed_at);
        $this->assertTrue($item->is_featured);

        $this->actingAs($principal)
            ->delete(route('admin.facebook-media.destroy', $item))
            ->assertRedirect();

        $this->assertSoftDeleted('facebook_media_items', ['id' => $item->id]);

        $this->actingAs($principal)
            ->patch(route('admin.trash.restore', ['type' => 'media', 'id' => $item->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('facebook_media_items', ['id' => $item->id, 'deleted_at' => null]);
    }

    public function test_media_library_delete_is_now_recoverable_and_permanent_delete_removes_file(): void
    {
        $this->withoutExceptionHandling();
        Storage::fake('public');

        $principal = $this->staff('principal');
        $this->actingAs($principal);

        $this->post(route('admin.media.store'), [
            'title' => 'Phase 28 Media Asset',
            'file' => $this->pngUpload('phase28-media.png'),
            'alt_text' => 'Phase 28 media asset',
            'caption' => 'Media audit.',
            'category' => 'General',
            'rights_confirmed' => '1',
        ])->assertRedirect(route('admin.media.index'));

        $asset = MediaAsset::where('title', 'Phase 28 Media Asset')->firstOrFail();
        $path = $asset->file_path;

        Storage::disk('public')->assertExists($path);

        $this->delete(route('admin.media.destroy', $asset))->assertRedirect();
        $this->assertSoftDeleted('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertExists($path);

        $this->get(route('admin.trash.index'))
            ->assertOk()
            ->assertSee('Media Library')
            ->assertSee('Phase 28 Media Asset');

        $this->patch(route('admin.trash.restore', ['type' => 'asset', 'id' => $asset->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('media_assets', ['id' => $asset->id, 'deleted_at' => null]);
        Storage::disk('public')->assertExists($path);

        $asset->refresh();
        $this->delete(route('admin.media.destroy', $asset))->assertRedirect();

        $super = $this->staff('super_admin');

        $this->actingAs($super)
            ->delete(route('admin.trash.destroy', ['type' => 'asset', 'id' => $asset->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_faculty_calendar_and_document_archive_actions_are_recoverable_in_safe_trash(): void
    {
        $this->withoutExceptionHandling();
        $principal = $this->staff('principal');
        $this->actingAs($principal);

        $this->post(route('admin.faculty.store'), [
            'name' => 'Phase 28 Teacher',
            'position' => 'Teacher',
            'department' => 'Elementary',
            'biography' => 'Functional audit profile.',
            'sort_order' => 0,
            'is_published' => '1',
        ])->assertRedirect(route('admin.faculty.index'));

        $faculty = FacultyProfile::where('name', 'Phase 28 Teacher')->firstOrFail();

        $this->patch(route('admin.faculty.update', $faculty), [
            'name' => 'Phase 28 Teacher Updated',
            'position' => 'Class Adviser',
            'department' => 'Elementary',
            'biography' => 'Updated profile.',
            'sort_order' => 1,
            'is_published' => '1',
        ])->assertRedirect(route('admin.faculty.index'));

        $faculty->refresh();
        $this->assertSame('Class Adviser', $faculty->position);

        $this->delete(route('admin.faculty.destroy', $faculty))->assertRedirect();
        $this->assertSoftDeleted('faculty_profiles', ['id' => $faculty->id]);

        $this->patch(route('admin.trash.restore', ['type' => 'faculty', 'id' => $faculty->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('faculty_profiles', ['id' => $faculty->id, 'deleted_at' => null]);

        $this->post(route('admin.calendar.store'), [
            'title' => 'Phase 28 Calendar Entry',
            'category' => 'academic',
            'description' => 'Calendar write audit.',
            'starts_at' => now()->addDays(8)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(8)->addHour()->format('Y-m-d H:i:s'),
            'school_year' => '2026-2027',
            'is_published' => '1',
        ])->assertRedirect(route('admin.calendar.index'));

        $calendar = AcademicCalendarEntry::where('title', 'Phase 28 Calendar Entry')->firstOrFail();

        $this->patch(route('admin.calendar.update', $calendar), [
            'title' => 'Phase 28 Calendar Entry Updated',
            'category' => 'activity',
            'description' => 'Updated calendar audit.',
            'starts_at' => now()->addDays(9)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(9)->addHours(2)->format('Y-m-d H:i:s'),
            'school_year' => '2026-2027',
            'is_published' => '1',
        ])->assertRedirect(route('admin.calendar.index'));

        $calendar->refresh();
        $this->assertSame('activity', $calendar->category);

        $this->delete(route('admin.calendar.destroy', $calendar))->assertRedirect();
        $this->assertSoftDeleted('academic_calendar_entries', ['id' => $calendar->id]);

        $this->patch(route('admin.trash.restore', ['type' => 'calendar', 'id' => $calendar->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('academic_calendar_entries', ['id' => $calendar->id, 'deleted_at' => null]);

        $this->documentCrudAndTrash($principal);
    }

    public function test_teacher_review_changes_requested_and_reapproval_workflow_executes(): void
    {
        $teacher = $this->staff('teacher');

        $this->actingAs($teacher)->post(route('admin.announcements.store'), [
            'title' => 'Phase 28 Review Draft',
            'excerpt' => 'Review flow.',
            'body' => 'First draft.',
            'type' => 'info',
            'audience' => 'public',
            'sort_order' => 0,
            'action' => 'submit_review',
        ])->assertRedirect();

        $item = Announcement::where('title', 'Phase 28 Review Draft')->firstOrFail();

        $this->assertSame('pending_review', $item->workflow_status);

        $principal = $this->staff('principal');

        $this->actingAs($principal)->patch(
            route('admin.reviews.decide', ['type' => 'announcement', 'id' => $item->id]),
            [
                'decision' => 'changes_requested',
                'review_notes' => 'Please clarify the notice.',
            ]
        )->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame('changes_requested', $item->workflow_status);
        $this->assertNull($item->published_at);

        $this->actingAs($teacher)->patch(route('admin.announcements.update', $item), [
            'title' => 'Phase 28 Review Draft Revised',
            'excerpt' => 'Review flow revised.',
            'body' => 'Clarified second draft.',
            'type' => 'info',
            'audience' => 'public',
            'sort_order' => 0,
            'action' => 'submit_review',
        ])->assertRedirect();

        $item->refresh();
        $this->assertSame('pending_review', $item->workflow_status);

        $this->actingAs($principal)->patch(
            route('admin.reviews.decide', ['type' => 'announcement', 'id' => $item->id]),
            [
                'decision' => 'approve',
                'review_notes' => 'Approved after revision.',
            ]
        )->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame('published', $item->workflow_status);
        $this->assertNotNull($item->published_at);
    }

    public function test_inquiry_assignment_status_and_followup_update_action_executes(): void
    {
        $principal = $this->staff('principal');
        $teacher = $this->staff('teacher');

        $inquiry = Inquiry::create([
            'guardian_name' => 'Phase 28 Parent',
            'email' => 'phase28-parent@example.test',
            'student_name' => 'Phase 28 Learner',
            'level_interested' => 'Grade 1',
            'message' => 'Functional audit inquiry.',
            'status' => 'new',
            'privacy_consent_at' => now(),
        ]);

        $this->actingAs($principal)->patch(route('admin.inquiries.update', $inquiry), [
            'status' => 'follow_up',
            'admin_notes' => 'Contact the family.',
            'assigned_to_user_id' => $teacher->id,
            'follow_up_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'source' => 'Website',
            'interest_level' => 'high',
            'last_contacted_at' => now()->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('admin.inquiries.show', $inquiry));

        $inquiry->refresh();

        $this->assertSame('follow_up', $inquiry->status);
        $this->assertSame($teacher->id, $inquiry->assigned_to_user_id);
        $this->assertSame('high', $inquiry->interest_level);
    }

    public function test_admissions_status_checklist_document_verification_download_and_access_rotation_execute(): void
    {
        $principal = $this->staff('principal');
        $application = $this->application();

        $this->actingAs($principal)
            ->get(route('admin.admissions.show', $application))
            ->assertOk();

        $application->refresh();
        $item = $application->checklistItems()->firstOrFail();

        $this->patch(route('admin.admissions.update', $application), [
            'status' => 'under_review',
            'public_status_message' => 'Your application is under review.',
            'admin_notes' => 'Phase 28 audit.',
        ])->assertSessionHasNoErrors();

        $application->refresh();
        $this->assertSame('under_review', $application->status);

        $this->patch(route('admin.admissions.checklist.update', [$application, $item]), [
            'is_completed' => '1',
            'notes' => 'Reviewed by Phase 28.',
        ])->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertTrue($item->is_completed);
        $this->assertSame($principal->id, $item->completed_by_user_id);

        $root = storage_path('app/private/admissions');
        File::ensureDirectoryExists($root);
        $storedName = 'phase28-admin-document.pdf';
        File::put($root.'/'.$storedName, '%PDF-1.4 phase28');

        try {
            $document = AdmissionDocument::create([
                'admission_application_id' => $application->id,
                'document_type' => 'report_card',
                'original_name' => 'phase28-report-card.pdf',
                'stored_name' => $storedName,
                'path' => $storedName,
                'mime_type' => 'application/pdf',
                'size_bytes' => 20,
                'uploaded_by' => 'family',
                'is_verified' => false,
            ]);

            $this->get(route('admin.admissions.documents.download', [$application, $document]))
                ->assertOk();

            $this->patch(route('admin.admissions.documents.verify', [$application, $document]), [
                'is_verified' => '1',
                'admin_notes' => 'Verified in Phase 28.',
            ])->assertSessionHasNoErrors();

            $document->refresh();
            $this->assertTrue($document->is_verified);
            $this->assertSame($principal->id, $document->verified_by_user_id);

            $oldHash = $application->access_code_hash;

            $this->post(route('admin.admissions.rotate-access-code', $application))
                ->assertSessionHas('new_access_code');

            $application->refresh();
            $this->assertNotSame($oldHash, $application->access_code_hash);
        } finally {
            File::delete($root.'/'.$storedName);
        }
    }

    public function test_staff_account_password_two_factor_reset_and_security_actions_execute(): void
    {
        $super = $this->staff('super_admin');
        $this->actingAs($super);

        $this->post(route('admin.staff.store'), [
            'name' => 'Phase 28 Teacher Account',
            'email' => 'phase28-teacher@example.test',
            'role' => 'teacher',
            'password' => 'StrongTeacher123',
            'password_confirmation' => 'StrongTeacher123',
            'force_password_reset' => '1',
        ])->assertRedirect(route('admin.staff.index'));

        $teacher = User::where('email', 'phase28-teacher@example.test')->firstOrFail();

        $this->patch(route('admin.staff.update', $teacher), [
            'name' => 'Phase 28 Teacher Updated',
            'email' => 'phase28-teacher-updated@example.test',
            'role' => 'teacher',
            'is_active' => '1',
            'force_password_reset' => '0',
        ])->assertRedirect(route('admin.staff.index'));

        $teacher->refresh();
        $this->assertSame('Phase 28 Teacher Updated', $teacher->name);
        $this->assertTrue($teacher->is_active);

        $teacher->forceFill([
            'two_factor_secret' => Totp::generateSecret(),
            'two_factor_enabled_at' => now(),
            'two_factor_recovery_codes' => [Hash::make('RECOVERY123')],
        ])->save();

        $this->post(route('admin.staff.reset-two-factor', $teacher))
            ->assertSessionHasNoErrors();

        $teacher->refresh();
        $this->assertNull($teacher->two_factor_secret);
        $this->assertNull($teacher->two_factor_enabled_at);
        $this->assertTrue($teacher->force_password_reset);

        $otherSuper = $this->staff('super_admin');

        $this->get(route('admin.staff.edit', $otherSuper))->assertForbidden();

        $this->patch(route('admin.security.password'), [
            'current_password' => 'password',
            'password' => 'StrongAdmin456',
            'password_confirmation' => 'StrongAdmin456',
        ])->assertSessionHasNoErrors();

        $super->refresh();
        $this->assertTrue(Hash::check('StrongAdmin456', $super->password));

        $this->post(route('admin.security.two-factor.setup'), [
            'current_password' => 'StrongAdmin456',
        ])->assertSessionHasNoErrors();

        $secret = (string) session('two_factor_setup_secret');
        $this->assertNotSame('', $secret);

        $code = $this->totpCode($secret);

        $this->post(route('admin.security.two-factor.confirm'), [
            'code' => $code,
        ])->assertSessionHasNoErrors()->assertSessionHas('new_recovery_codes');

        $super->refresh();
        $this->assertTrue($super->twoFactorEnabled());

        $recoveryCodes = session('new_recovery_codes');
        $this->assertIsArray($recoveryCodes);
        $this->assertCount(8, $recoveryCodes);

        $this->delete(route('admin.security.two-factor.disable'), [
            'current_password' => 'StrongAdmin456',
            'code' => $recoveryCodes[0],
        ])->assertSessionHasNoErrors();

        $super->refresh();
        $this->assertFalse($super->twoFactorEnabled());

        $this->post(route('admin.security.revoke-sessions'), [
            'current_password' => 'StrongAdmin456',
        ])->assertSessionHasNoErrors();
    }

    public function test_role_restrictions_protect_leadership_and_permanent_delete_actions(): void
    {
        $teacher = $this->staff('teacher');

        foreach ([
            'admin.trash.index',
            'admin.audit.index',
            'admin.reviews.index',
            'admin.faculty.index',
            'admin.documents.index',
            'admin.calendar.index',
            'admin.admissions.index',
            'admin.inquiries.index',
            'admin.settings.edit',
            'admin.branding.edit',
        ] as $routeName) {
            $this->actingAs($teacher)->get(route($routeName))->assertForbidden();
        }

        $announcement = Announcement::create([
            'title' => 'Phase 28 Protected Delete',
            'body' => 'Protected.',
            'type' => 'info',
            'sort_order' => 0,
        ]);
        $announcement->delete();

        $principal = $this->staff('principal');

        $this->actingAs($principal)
            ->delete(route('admin.trash.destroy', ['type' => 'announcement', 'id' => $announcement->id]))
            ->assertForbidden();

        $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);

        $this->actingAs($principal)
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }

    private function documentCrudAndTrash(User $principal): void
    {
        $root = storage_path('app/private/documents');
        File::ensureDirectoryExists($root);

        $this->actingAs($principal)->post(route('admin.documents.store'), [
            'title' => 'Phase 28 Public Document',
            'description' => 'Document write-action audit.',
            'category' => 'Handbook',
            'file' => UploadedFile::fake()->create('phase28-handbook.pdf', 50, 'application/pdf'),
            'school_year' => '2026-2027',
            'audience' => 'public',
            'sort_order' => 0,
            'is_published' => '1',
        ])->assertRedirect(route('admin.documents.index'));

        $document = SchoolDocument::where('title', 'Phase 28 Public Document')->firstOrFail();
        $filePath = $root.'/'.$document->file_path;

        try {
            $this->assertTrue(File::exists($filePath));

            $this->patch(route('admin.documents.update', $document), [
                'title' => 'Phase 28 Public Document Updated',
                'description' => 'Updated document audit.',
                'category' => 'Handbook',
                'school_year' => '2026-2027',
                'audience' => 'public',
                'sort_order' => 1,
                'is_published' => '1',
            ])->assertRedirect(route('admin.documents.index'));

            $document->refresh();
            $this->assertSame('Phase 28 Public Document Updated', $document->title);

            $this->get(route('admin.documents.download', $document))->assertOk();

            $this->delete(route('admin.documents.destroy', $document))->assertRedirect();
            $this->assertSoftDeleted('school_documents', ['id' => $document->id]);
            $this->assertTrue(File::exists($filePath));

            $this->patch(route('admin.trash.restore', ['type' => 'document', 'id' => $document->id]))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('school_documents', ['id' => $document->id, 'deleted_at' => null]);

            $document->refresh();
            $this->delete(route('admin.documents.destroy', $document))->assertRedirect();

            $super = $this->staff('super_admin');

            $this->actingAs($super)
                ->delete(route('admin.trash.destroy', ['type' => 'document', 'id' => $document->id]))
                ->assertSessionHasNoErrors();

            $this->assertDatabaseMissing('school_documents', ['id' => $document->id]);
            $this->assertFalse(File::exists($filePath));
        } finally {
            File::delete($filePath);
        }
    }

    private function pngUpload(string $name): UploadedFile
    {
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);

        if (! is_string($content)) {
            throw new \RuntimeException('Embedded Phase 28 PNG fixture could not be decoded.');
        }

        return UploadedFile::fake()->createWithContent($name, $content);
    }
    private function application(): AdmissionApplication
    {
        return AdmissionApplication::create([
            'reference_code' => AdmissionApplication::createReferenceCode(),
            'access_code_hash' => Hash::make('ABCD1234EFGH'),
            'guardian_name' => 'Phase 28 Guardian',
            'guardian_email' => 'phase28-guardian@example.test',
            'student_name' => 'Phase 28 Learner',
            'applying_for_level' => 'Grade 4',
            'school_year' => '2026-2027',
            'status' => 'submitted',
            'privacy_consent_at' => now(),
            'application_consent_at' => now(),
            'submitted_at' => now(),
        ]);
    }

    private function staff(string $role): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function totpCode(string $secret): string
    {
        $method = new \ReflectionMethod(Totp::class, 'code');
        $method->setAccessible(true);

        return (string) $method->invoke(null, $secret, intdiv(time(), 30));
    }
}
