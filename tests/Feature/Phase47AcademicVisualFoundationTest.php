<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentTeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase47AcademicVisualFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_request_existing_student_without_duplicate_record_and_requires_approval(): void
    {
        $principal = $this->staff('principal', 'principal@nacs.test');
        $teacher = $this->staff('teacher', 'teacher@nacs.test');

        $student = Student::create([
            'student_number' => 'NACS-2026-0001',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'date_of_birth' => '2014-05-07',
            'grade_level' => 'Grade 6',
            'school_year' => '2026-2027',
            'status' => 'active',
            'classification' => 'confidential',
            'created_by' => $principal->id,
        ]);

        $this->actingAs($teacher)
            ->post(route('admin.students.assignments.request-existing'), [
                'student_number' => 'NACS-2026-0001',
                'date_of_birth' => '2014-05-07',
                'subject' => 'Mathematics',
            ])
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseCount('students', 1);

        $assignment = StudentTeacherAssignment::query()->firstOrFail();
        $this->assertSame('pending', $assignment->status);

        $this->actingAs($teacher)
            ->get(route('admin.students.show', $student))
            ->assertForbidden();

        $this->actingAs($principal)
            ->patch(route('admin.students.assignments.approve', [$student, $assignment]))
            ->assertRedirect();

        $this->actingAs($teacher)
            ->get(route('admin.students.show', $student))
            ->assertOk();
    }

    public function test_report_card_and_transcript_are_generated_from_one_student_record(): void
    {
        $principal = $this->staff('principal', 'principal2@nacs.test');
        $teacher = $this->staff('teacher', 'teacher2@nacs.test');

        $student = Student::create([
            'student_number' => 'NACS-2026-0002',
            'first_name' => 'Daniel',
            'last_name' => 'Reyes',
            'date_of_birth' => '2013-02-04',
            'grade_level' => 'Grade 7',
            'school_year' => '2026-2027',
            'status' => 'active',
            'classification' => 'confidential',
            'created_by' => $principal->id,
        ]);

        $student->assignments()->create([
            'teacher_id' => $teacher->id,
            'school_year' => '2026-2027',
            'subject' => 'Science',
            'is_adviser' => false,
            'can_manage_profile' => false,
            'can_manage_grades' => true,
            'can_manage_attendance' => true,
            'status' => 'active',
            'approved_by' => $principal->id,
            'approved_at' => now(),
        ]);

        $student->grades()->create([
            'teacher_id' => $teacher->id,
            'school_year' => '2026-2027',
            'subject' => 'Science',
            'term' => 'Q1',
            'category' => 'final_grade',
            'assessment_name' => 'Quarter 1 Grade',
            'grade_percentage' => 92.50,
        ]);

        $this->actingAs($teacher)
            ->get(route('admin.students.report-card', $student))
            ->assertOk()
            ->assertSee('Student Report Card')
            ->assertSee('Science')
            ->assertSee('92.50');

        $this->actingAs($teacher)
            ->get(route('admin.students.transcript', $student).'?official=1')
            ->assertForbidden();

        $this->actingAs($principal)
            ->get(route('admin.students.transcript', $student).'?official=1')
            ->assertOk()
            ->assertSee('Transcript of Records')
            ->assertSee('OFFICIAL PRINT VIEW');
    }

    public function test_visual_homepage_editor_has_locked_live_preview_and_image_requirements(): void
    {
        $admin = $this->staff('super_admin', 'admin47@nacs.test');

        $this->actingAs($admin)
            ->get(route('admin.website-content.edit'))
            ->assertOk()
            ->assertSee('Visual Homepage Editor')
            ->assertSee('LOCKED DESIGN')
            ->assertSee('minimum 1200 x 750 px')
            ->assertSee('data-ve-frame', false)
            ->assertSee('data-ve-field', false)
            ->assertSee('name="hero_badge"', false)
            ->assertSee('name="hero_heading"', false)
            ->assertSee('name="hero_highlight"', false)
            ->assertSee('name="contact_email"', false)
            ->assertSee('name="hero_image_focus_x"', false)
            ->assertDontSee('name="0"', false);
    }


    public function test_student_profile_photo_is_kept_on_private_disk_without_gd_dependency(): void
    {
        Storage::fake('local');

        $principal = $this->staff('principal', 'principal-photo@nacs.test');
        $student = Student::create([
            'student_number' => 'NACS-2026-0099',
            'first_name' => 'Photo',
            'last_name' => 'Student',
            'date_of_birth' => '2015-01-01',
            'grade_level' => 'Grade 5',
            'school_year' => '2026-2027',
            'status' => 'active',
            'classification' => 'confidential',
            'created_by' => $principal->id,
        ]);

        // UploadedFile::fake()->image() requires PHP GD. NACS-Phil does not
        // require GD just to validate/store an uploaded PNG, so this test uses
        // a real deterministic 600x600 PNG fixture instead of manufacturing
        // an image through GD.
        $temporaryPath = tempnam(sys_get_temp_dir(), 'nacs-photo-');
        $this->assertNotFalse($temporaryPath);

        try {
            file_put_contents($temporaryPath, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAlgAAAJYCAIAAAAxBA+LAAAHLElEQVR42u3VMQ0AAAgEsfevlJUEAbhgoUkV3HKZLgB4KxIAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAGKEKABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIgBGqAIARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAmCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEABihBAAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQBGqAIARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARgiAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQJghCoAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEARigBAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIgBGqAIARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAsCFBXkDgA5jY4ByAAAAAElFTkSuQmCC',
                true
            ));

            $photo = new UploadedFile(
                $temporaryPath,
                'student-profile.png',
                'image/png',
                null,
                true
            );

            $this->actingAs($principal)
                ->post(route('admin.students.photo.store', $student), [
                    'profile_photo' => $photo,
                ])
                ->assertRedirect();

            $student->refresh();

            $this->assertSame('local', $student->profile_photo_disk);
            $this->assertNotNull($student->profile_photo_path);
            $this->assertSame('image/png', $student->profile_photo_mime_type);
            Storage::disk('local')->assertExists($student->profile_photo_path);
        } finally {
            if (is_string($temporaryPath) && file_exists($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    public function test_embedded_profile_photo_fixture_is_valid_without_gd(): void
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'nacs-fixture-');
        $this->assertNotFalse($temporaryPath);

        try {
            file_put_contents($temporaryPath, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAlgAAAJYCAIAAAAxBA+LAAAHLElEQVR42u3VMQ0AAAgEsfevlJUEAbhgoUkV3HKZLgB4KxIAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAGKEKABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghABghAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIgBGqAIARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAmCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEABihBAAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQBGqAIARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARggARgiAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQKAEQJghCoAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAYIQAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEAGCEARigBAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIAEYIgBGqAIARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAoARAsCFBXkDgA5jY4ByAAAAAElFTkSuQmCC',
                true
            ));

            $size = getimagesize($temporaryPath);

            $this->assertIsArray($size);
            $this->assertSame(600, $size[0]);
            $this->assertSame(600, $size[1]);
            $this->assertSame('image/png', $size['mime']);
        } finally {
            if (is_string($temporaryPath) && file_exists($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    public function test_payment_schema_is_provider_neutral_and_disabled_by_default(): void
    {
        $this->assertTrue(Schema::hasTable('student_payment_transactions'));
        $this->assertFalse(config('payments.enabled'));
        $this->assertSame('PHP', config('payments.currency'));
    }

    private function staff(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst(str_replace('_', ' ', $role)),
            'email' => $email,
            'password' => Hash::make('Phase47!StrongPass2026'),
            'is_admin' => true,
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
            'password_changed_at' => now(),
            'force_password_reset' => false,
        ]);
    }
}
