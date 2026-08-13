<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faculty_profiles')) {
            Schema::create('faculty_profiles', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 180);
                $table->string('position', 180);
                $table->string('department', 120)->nullable();
                $table->text('biography')->nullable();
                $table->text('credentials')->nullable();
                $table->string('grade_subject', 180)->nullable();
                $table->string('photo_path', 500)->nullable();
                $table->string('alt_text', 250)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_published')->default(false);
                $table->timestamp('consent_confirmed_at')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('school_documents')) {
            Schema::create('school_documents', function (Blueprint $table): void {
                $table->id();
                $table->string('title', 180);
                $table->string('slug', 220)->unique();
                $table->text('description')->nullable();
                $table->string('category', 100);
                $table->string('file_path', 500);
                $table->string('original_name', 255);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('school_year', 30)->nullable();
                $table->string('audience', 30)->default('public');
                $table->timestamp('published_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('academic_calendar_entries')) {
            Schema::create('academic_calendar_entries', function (Blueprint $table): void {
                $table->id();
                $table->string('title', 180);
                $table->string('category', 60)->default('academic');
                $table->text('description')->nullable();
                $table->dateTime('starts_at');
                $table->dateTime('ends_at');
                $table->boolean('is_all_day')->default(false);
                $table->string('school_year', 30)->nullable();
                $table->boolean('is_published')->default(false);
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('media_assets')) {
            Schema::create('media_assets', function (Blueprint $table): void {
                $table->id();
                $table->string('title', 180);
                $table->string('file_path', 500);
                $table->string('original_name', 255);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('alt_text', 250)->nullable();
                $table->text('caption')->nullable();
                $table->string('category', 100)->nullable();
                $table->timestamp('rights_confirmed_at')->nullable();
                $table->timestamp('consent_confirmed_at')->nullable();
                $table->string('credit', 180)->nullable();
                $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('admission_checklist_items')) {
            Schema::create('admission_checklist_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admission_application_id')->constrained('admission_applications')->cascadeOnDelete();
                $table->string('item_key', 80);
                $table->string('label', 180);
                $table->boolean('is_required')->default(true);
                $table->boolean('is_completed')->default(false);
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['admission_application_id', 'item_key'], 'admission_checklist_unique');
            });
        }

        if (! Schema::hasTable('seo_settings')) {
            Schema::create('seo_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('page_key', 100)->unique();
                $table->string('title', 180)->nullable();
                $table->string('meta_description', 320)->nullable();
                $table->string('social_title', 180)->nullable();
                $table->string('social_description', 320)->nullable();
                $table->string('social_image_path', 500)->nullable();
                $table->boolean('no_index')->default(false);
                $table->string('canonical_url', 500)->nullable();
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('school_settings')) {
            Schema::create('school_settings', function (Blueprint $table): void {
                $table->id();
                $table->string('key', 120)->unique();
                $table->text('value')->nullable();
                $table->string('group', 60)->default('general');
                $table->boolean('is_public')->default(true);
                $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        $this->addAnnouncementColumns();
        $this->addEventColumns();
        $this->addGalleryColumns();
        $this->addInquiryColumns();
        $this->addUserSecurityColumns();
    }

    private function addAnnouncementColumns(): void
    {
        if (! Schema::hasColumn('announcements', 'workflow_status')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->string('workflow_status', 30)->default('published')->after('published_at'));
        }
        if (! Schema::hasColumn('announcements', 'submitted_for_review_at')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->timestamp('submitted_for_review_at')->nullable());
        }
        if (! Schema::hasColumn('announcements', 'reviewed_at')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->timestamp('reviewed_at')->nullable());
        }
        if (! Schema::hasColumn('announcements', 'reviewed_by_user_id')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->unsignedBigInteger('reviewed_by_user_id')->nullable());
        }
        if (! Schema::hasColumn('announcements', 'review_notes')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->text('review_notes')->nullable());
        }
        if (! Schema::hasColumn('announcements', 'scheduled_publish_at')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->timestamp('scheduled_publish_at')->nullable());
        }
        if (! Schema::hasColumn('announcements', 'audience')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->string('audience', 30)->default('public'));
        }
        if (! Schema::hasColumn('announcements', 'is_pinned')) {
            Schema::table('announcements', fn (Blueprint $table) => $table->boolean('is_pinned')->default(false));
        }
    }

    private function addEventColumns(): void
    {
        if (! Schema::hasColumn('school_events', 'workflow_status')) {
            Schema::table('school_events', fn (Blueprint $table) => $table->string('workflow_status', 30)->default('published')->after('published_at'));
        }
        if (! Schema::hasColumn('school_events', 'submitted_for_review_at')) {
            Schema::table('school_events', fn (Blueprint $table) => $table->timestamp('submitted_for_review_at')->nullable());
        }
        if (! Schema::hasColumn('school_events', 'reviewed_at')) {
            Schema::table('school_events', fn (Blueprint $table) => $table->timestamp('reviewed_at')->nullable());
        }
        if (! Schema::hasColumn('school_events', 'reviewed_by_user_id')) {
            Schema::table('school_events', fn (Blueprint $table) => $table->unsignedBigInteger('reviewed_by_user_id')->nullable());
        }
        if (! Schema::hasColumn('school_events', 'review_notes')) {
            Schema::table('school_events', fn (Blueprint $table) => $table->text('review_notes')->nullable());
        }
        if (! Schema::hasColumn('school_events', 'scheduled_publish_at')) {
            Schema::table('school_events', fn (Blueprint $table) => $table->timestamp('scheduled_publish_at')->nullable());
        }
    }

    private function addGalleryColumns(): void
    {
        if (! Schema::hasColumn('gallery_items', 'workflow_status')) {
            Schema::table('gallery_items', fn (Blueprint $table) => $table->string('workflow_status', 30)->default('published')->after('is_published'));
        }
        if (! Schema::hasColumn('gallery_items', 'submitted_for_review_at')) {
            Schema::table('gallery_items', fn (Blueprint $table) => $table->timestamp('submitted_for_review_at')->nullable());
        }
        if (! Schema::hasColumn('gallery_items', 'reviewed_at')) {
            Schema::table('gallery_items', fn (Blueprint $table) => $table->timestamp('reviewed_at')->nullable());
        }
        if (! Schema::hasColumn('gallery_items', 'reviewed_by_user_id')) {
            Schema::table('gallery_items', fn (Blueprint $table) => $table->unsignedBigInteger('reviewed_by_user_id')->nullable());
        }
        if (! Schema::hasColumn('gallery_items', 'review_notes')) {
            Schema::table('gallery_items', fn (Blueprint $table) => $table->text('review_notes')->nullable());
        }
    }

    private function addInquiryColumns(): void
    {
        if (! Schema::hasColumn('inquiries', 'assigned_to_user_id')) {
            Schema::table('inquiries', fn (Blueprint $table) => $table->unsignedBigInteger('assigned_to_user_id')->nullable());
        }
        if (! Schema::hasColumn('inquiries', 'follow_up_at')) {
            Schema::table('inquiries', fn (Blueprint $table) => $table->timestamp('follow_up_at')->nullable());
        }
        if (! Schema::hasColumn('inquiries', 'source')) {
            Schema::table('inquiries', fn (Blueprint $table) => $table->string('source', 80)->nullable());
        }
        if (! Schema::hasColumn('inquiries', 'interest_level')) {
            Schema::table('inquiries', fn (Blueprint $table) => $table->string('interest_level', 30)->nullable());
        }
        if (! Schema::hasColumn('inquiries', 'last_contacted_at')) {
            Schema::table('inquiries', fn (Blueprint $table) => $table->timestamp('last_contacted_at')->nullable());
        }
    }

    private function addUserSecurityColumns(): void
    {
        if (! Schema::hasColumn('users', 'last_login_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('last_login_at')->nullable());
        }
        if (! Schema::hasColumn('users', 'last_login_ip_hash')) {
            Schema::table('users', fn (Blueprint $table) => $table->string('last_login_ip_hash', 64)->nullable());
        }
        if (! Schema::hasColumn('users', 'failed_login_count')) {
            Schema::table('users', fn (Blueprint $table) => $table->unsignedInteger('failed_login_count')->default(0));
        }
        if (! Schema::hasColumn('users', 'locked_until')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('locked_until')->nullable());
        }
        if (! Schema::hasColumn('users', 'password_changed_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('password_changed_at')->nullable());
        }
        if (! Schema::hasColumn('users', 'force_password_reset')) {
            Schema::table('users', fn (Blueprint $table) => $table->boolean('force_password_reset')->default(false));
        }
        if (! Schema::hasColumn('users', 'two_factor_secret')) {
            Schema::table('users', fn (Blueprint $table) => $table->text('two_factor_secret')->nullable());
        }
        if (! Schema::hasColumn('users', 'two_factor_enabled_at')) {
            Schema::table('users', fn (Blueprint $table) => $table->timestamp('two_factor_enabled_at')->nullable());
        }
        if (! Schema::hasColumn('users', 'two_factor_recovery_codes')) {
            Schema::table('users', fn (Blueprint $table) => $table->text('two_factor_recovery_codes')->nullable());
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive. Phase 12 contains school-content and
        // security metadata that should not be silently removed by rollback.
    }
};
