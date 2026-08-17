<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\HeaderContentController;
use App\Support\StaffAccess;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase55HeaderManagerTest extends TestCase
{
    public function test_header_management_permission_is_limited_to_appropriate_editor_roles(): void
    {
        $this->assertTrue(StaffAccess::roleHasPermission('principal', 'header.manage'));
        $this->assertTrue(StaffAccess::roleHasPermission('frontend_editor', 'header.manage'));
        $this->assertTrue(StaffAccess::roleHasPermission('text_editor', 'header.manage'));

        $this->assertFalse(StaffAccess::roleHasPermission('news_editor', 'header.manage'));
        $this->assertFalse(StaffAccess::roleHasPermission('events_editor', 'header.manage'));
        $this->assertFalse(StaffAccess::roleHasPermission('media_editor', 'header.manage'));
        $this->assertFalse(StaffAccess::roleHasPermission('teacher', 'header.manage'));
    }

    public function test_header_manager_routes_and_fields_exist(): void
    {
        $this->assertTrue(Route::has('admin.header.edit'));
        $this->assertTrue(Route::has('admin.header.update'));

        foreach ([
            'header_short_name',
            'header_school_name',
            'header_nav_home',
            'header_resources_label',
            'header_enroll_label',
            'document_watermark',
        ] as $key) {
            $this->assertArrayHasKey($key, HeaderContentController::FIELDS);
        }
    }

    public function test_public_header_uses_managed_settings_and_links_learning_tools(): void
    {
        $source = (string) file_get_contents(resource_path('views/partials/public-header.blade.php'));

        $this->assertStringContainsString("header_short_name", $source);
        $this->assertStringContainsString("header_school_name", $source);
        $this->assertStringContainsString("header_resources_label", $source);
        $this->assertStringContainsString("header_enroll_label", $source);
        $this->assertStringContainsString("route('learning-tools.index')", $source);
    }

    public function test_academic_pdf_branding_uses_same_managed_header_source(): void
    {
        $source = (string) file_get_contents(app_path('Support/SchoolDocumentBranding.php'));

        $this->assertStringContainsString("'header_school_name'", $source);
        $this->assertStringContainsString("'header_short_name'", $source);
        $this->assertStringContainsString("'document_watermark'", $source);
    }
}
