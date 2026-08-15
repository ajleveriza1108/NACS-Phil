<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase41StudentInformationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_registers_student_and_receives_only_that_assignment(): void
    {
        $teacher = User::factory()->create([
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $otherTeacher = User::factory()->create([
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($teacher)->post(route('admin.students.store'), [
            'student_number' => 'NACS-2026-001',
            'first_name' => 'Juan',
            'last_name' => 'Santos',
            'grade_level' => 'Grade 6',
            'section' => 'Faith',
            'school_year' => '2026-2027',
            'status' => 'active',
        ]);

        $student = Student::query()->where('student_number', 'NACS-2026-001')->firstOrFail();

        $response->assertRedirect(route('admin.students.show', $student));
        $this->assertTrue($student->assignments()->where('teacher_id', $teacher->id)->exists());

        $this->actingAs($teacher)->get(route('admin.students.show', $student))->assertOk();
        $this->actingAs($otherTeacher)->get(route('admin.students.show', $student))->assertForbidden();
    }

    public function test_assigned_teacher_can_record_grade_but_cannot_record_finance(): void
    {
        $teacher = User::factory()->create([
            'is_admin' => true,
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $student = Student::create([
            'student_number' => 'NACS-2026-002',
            'first_name' => 'Maria',
            'last_name' => 'Reyes',
            'grade_level' => 'Grade 7',
            'section' => 'Grace',
            'school_year' => '2026-2027',
            'status' => 'active',
            'created_by' => $teacher->id,
        ]);

        $student->assignments()->create([
            'teacher_id' => $teacher->id,
            'school_year' => '2026-2027',
            'subject' => 'Mathematics',
            'can_manage_profile' => false,
            'can_manage_grades' => true,
            'can_manage_attendance' => true,
        ]);

        $this->actingAs($teacher)->post(route('admin.students.grades.store', $student), [
            'subject' => 'Mathematics',
            'term' => 'Q1',
            'category' => 'exam',
            'assessment_name' => 'Quarterly Exam',
            'score' => 46,
            'max_score' => 50,
            'assessment_date' => '2026-08-15',
        ])->assertRedirect();

        $this->assertDatabaseHas('student_grades', [
            'student_id' => $student->id,
            'subject' => 'Mathematics',
            'assessment_name' => 'Quarterly Exam',
            'grade_percentage' => 92,
        ]);

        $this->actingAs($teacher)->post(route('admin.students.finance.store', $student), [
            'entry_type' => 'charge',
            'description' => 'Tuition',
            'amount' => 1000,
            'entry_date' => '2026-08-15',
        ])->assertForbidden();
    }

    public function test_leadership_can_view_all_students_and_manage_finance(): void
    {
        $principal = User::factory()->create([
            'is_admin' => true,
            'role' => 'principal',
            'is_active' => true,
        ]);

        $student = Student::create([
            'student_number' => 'NACS-2026-003',
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'grade_level' => 'Grade 5',
            'school_year' => '2026-2027',
            'status' => 'active',
            'created_by' => $principal->id,
        ]);

        $this->actingAs($principal)->get(route('admin.students.show', $student))->assertOk();

        $this->actingAs($principal)->post(route('admin.students.finance.store', $student), [
            'entry_type' => 'charge',
            'description' => 'Tuition assessment',
            'amount' => 2500,
            'entry_date' => '2026-08-15',
        ])->assertRedirect();

        $this->assertDatabaseHas('student_financial_entries', [
            'student_id' => $student->id,
            'description' => 'Tuition assessment',
            'amount' => 2500,
        ]);
    }

    public function test_student_and_parent_portals_are_isolated_and_no_store(): void
    {
        $studentUser = User::factory()->create([
            'is_admin' => false,
            'role' => 'student',
            'is_active' => true,
            'force_password_reset' => false,
        ]);

        $parentUser = User::factory()->create([
            'is_admin' => false,
            'role' => 'parent',
            'is_active' => true,
            'force_password_reset' => false,
        ]);

        $otherStudentUser = User::factory()->create([
            'is_admin' => false,
            'role' => 'student',
            'is_active' => true,
            'force_password_reset' => false,
        ]);

        $student = Student::create([
            'user_id' => $studentUser->id,
            'student_number' => 'NACS-2026-004',
            'first_name' => 'Paolo',
            'last_name' => 'Diaz',
            'grade_level' => 'Grade 8',
            'school_year' => '2026-2027',
            'status' => 'active',
        ]);

        $other = Student::create([
            'user_id' => $otherStudentUser->id,
            'student_number' => 'NACS-2026-005',
            'first_name' => 'Lia',
            'last_name' => 'Garcia',
            'grade_level' => 'Grade 8',
            'school_year' => '2026-2027',
            'status' => 'active',
        ]);

        $student->guardians()->create([
            'user_id' => $parentUser->id,
            'relationship' => 'Parent',
            'is_primary' => true,
            'can_view_finance' => true,
        ]);

        $studentResponse = $this->actingAs($studentUser)->get(route('portal.students.show', $student));
        $studentResponse->assertOk();
        $this->assertStringContainsString('no-store', (string) $studentResponse->headers->get('Cache-Control'));
        $this->assertSame('noindex, nofollow', $studentResponse->headers->get('X-Robots-Tag'));

        $this->actingAs($studentUser)->get(route('portal.students.show', $other))->assertForbidden();
        $this->actingAs($parentUser)->get(route('portal.students.show', $student))->assertOk();
        $this->actingAs($parentUser)->get(route('portal.students.show', $other))->assertForbidden();
    }

    public function test_confidential_student_documents_have_no_local_storage_fallback(): void
    {
        $this->assertSame('external', config('student_portal.documents.storage'));
        $this->assertFalse(config('student_portal.documents.allow_local_fallback'));

        $contents = file_get_contents(base_path('PHASE41_SECURE_STUDENT_INFORMATION_SYSTEM.md'));
        $this->assertStringContainsString('must not consume permanent web-host storage', $contents);
        $this->assertStringContainsString('No `.edu.ph` name is hard-coded', $contents);
    }
}
