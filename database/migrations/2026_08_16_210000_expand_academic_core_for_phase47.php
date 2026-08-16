<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->string('profile_photo_disk', 64)->nullable();
            $table->string('profile_photo_path', 512)->nullable();
            $table->string('profile_photo_mime_type', 120)->nullable();
            $table->unsignedBigInteger('profile_photo_size_bytes')->nullable();
        });

        Schema::table('student_teacher_assignments', function (Blueprint $table): void {
            $table->string('status', 24)->default('active');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->index(['student_id', 'status']);
            $table->index(['teacher_id', 'status']);
        });

        Schema::create('student_payment_transactions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider', 80);
            $table->string('external_reference', 180)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('PHP');
            $table->string('status', 32)->default('pending');
            $table->string('payment_method', 80)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->string('classification', 32)->default('highly_confidential');
            $table->timestamps();

            $table->unique(['provider', 'external_reference'], 'student_payment_provider_reference_unique');
            $table->index(['student_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_payment_transactions');

        Schema::table('student_teacher_assignments', function (Blueprint $table): void {
            $table->dropIndex(['student_id', 'status']);
            $table->dropIndex(['teacher_id', 'status']);
            $table->dropConstrainedForeignId('requested_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at']);
        });

        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn([
                'profile_photo_disk',
                'profile_photo_path',
                'profile_photo_mime_type',
                'profile_photo_size_bytes',
            ]);
        });
    }
};
