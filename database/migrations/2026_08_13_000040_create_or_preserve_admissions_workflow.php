<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admission_applications')) {
            Schema::create('admission_applications', function (Blueprint $table): void {
                $table->id();
                $table->string('reference_code', 32)->unique();
                $table->string('access_code_hash');
                $table->string('guardian_name', 120);
                $table->string('guardian_email', 180)->nullable()->index();
                $table->string('guardian_phone', 40)->nullable();
                $table->string('student_name', 120);
                $table->date('date_of_birth')->nullable();
                $table->string('applying_for_level', 80)->index();
                $table->string('school_year', 20)->index();
                $table->string('previous_school', 180)->nullable();
                $table->text('home_address')->nullable();
                $table->text('family_notes')->nullable();
                $table->string('status', 40)->default('submitted')->index();
                $table->text('public_status_message')->nullable();
                $table->text('admin_notes')->nullable();
                $table->dateTime('privacy_consent_at');
                $table->dateTime('application_consent_at');
                $table->dateTime('submitted_at')->index();
                $table->dateTime('last_viewed_at')->nullable();
                $table->dateTime('access_code_rotated_at')->nullable();
                $table->string('ip_hash', 64)->nullable();
                $table->string('user_agent', 500)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admission_documents')) {
            Schema::create('admission_documents', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admission_application_id')->constrained()->cascadeOnDelete();
                $table->string('document_type', 80)->index();
                $table->string('original_name', 255);
                $table->string('stored_name', 255);
                $table->string('path', 500);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->string('uploaded_by', 20)->default('applicant')->index();
                $table->boolean('is_verified')->default(false)->index();
                $table->dateTime('verified_at')->nullable();
                $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('admin_notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admission_events')) {
            Schema::create('admission_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admission_application_id')->constrained()->cascadeOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event_type', 50)->index();
                $table->string('old_status', 40)->nullable();
                $table->string('new_status', 40)->nullable();
                $table->text('public_message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Admissions records require an explicit
        // school-approved retention/deletion decision before removal.
    }
};