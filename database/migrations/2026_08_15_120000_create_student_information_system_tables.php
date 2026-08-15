<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('student_number', 64)->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('preferred_name', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 32)->nullable();
            $table->string('phone', 64)->nullable();
            $table->text('home_address')->nullable();
            $table->string('grade_level', 64);
            $table->string('section', 100)->nullable();
            $table->string('school_year', 32);
            $table->string('status', 32)->default('active');
            $table->string('classification', 32)->default('confidential');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_year', 'grade_level', 'section']);
            $table->index(['last_name', 'first_name']);
        });

        Schema::create('student_teacher_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('school_year', 32);
            $table->string('subject', 100)->nullable();
            $table->boolean('is_adviser')->default(false);
            $table->boolean('can_manage_profile')->default(false);
            $table->boolean('can_manage_grades')->default(true);
            $table->boolean('can_manage_attendance')->default(true);
            $table->timestamps();

            $table->index(['teacher_id', 'school_year']);
            $table->index(['student_id', 'school_year']);
        });

        Schema::create('student_guardians', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship', 64);
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_view_finance')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'user_id']);
        });

        Schema::create('student_grades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('school_year', 32);
            $table->string('subject', 100);
            $table->string('term', 32);
            $table->string('category', 64);
            $table->string('assessment_name', 160);
            $table->decimal('score', 10, 2)->nullable();
            $table->decimal('max_score', 10, 2)->nullable();
            $table->decimal('grade_percentage', 6, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->date('assessment_date')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'school_year', 'subject']);
        });

        Schema::create('student_attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('attendance_date');
            $table->string('status', 32);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'attendance_date']);
        });

        Schema::create('student_financial_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('school_year', 32);
            $table->string('entry_type', 32);
            $table->string('description', 180);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number', 100)->nullable();
            $table->date('entry_date');
            $table->date('due_date')->nullable();
            $table->string('classification', 32)->default('highly_confidential');
            $table->timestamps();

            $table->index(['student_id', 'school_year', 'entry_date']);
        });

        Schema::create('student_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_type', 80);
            $table->string('provider', 64);
            $table->string('external_id', 512);
            $table->string('display_name', 255);
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('classification', 32)->default('highly_confidential');
            $table->timestamps();

            $table->unique(['provider', 'external_id'], 'student_documents_provider_external_unique');
        });

        Schema::create('student_record_audits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 80);
            $table->string('record_type', 100);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('changed_fields')->nullable();
            $table->string('summary', 255);
            $table->timestamps();

            $table->index(['student_id', 'created_at']);
            $table->index(['actor_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_record_audits');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('student_financial_entries');
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('student_grades');
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('student_teacher_assignments');
        Schema::dropIfExists('students');
    }
};
