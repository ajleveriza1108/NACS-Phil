<?php

namespace Tests\Feature;

use App\Support\AcademicPdfService;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class Phase54AcademicPdfExportTest extends TestCase
{
    public function test_dompdf_is_installed_and_static_pdf_service_generates_a_pdf(): void
    {
        $this->assertTrue(class_exists(Dompdf::class));

        $pdf = app(AcademicPdfService::class)->render(
            '<!doctype html><html><body><h1>NACS-Phil PDF Test</h1></body></html>'
        );

        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertGreaterThan(500, strlen($pdf));
    }

    public function test_report_card_and_tor_pdf_routes_exist_for_admin_and_portal(): void
    {
        foreach ([
            'admin.students.report-card.pdf',
            'admin.students.transcript.pdf',
            'portal.students.report-card.pdf',
            'portal.students.transcript.pdf',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), $routeName);
        }
    }

    public function test_pdf_templates_are_a4_static_and_watermarked(): void
    {
        foreach ([
            resource_path('views/academic/pdf/report-card.blade.php'),
            resource_path('views/academic/pdf/transcript.blade.php'),
        ] as $path) {
            $source = (string) file_get_contents($path);

            $this->assertStringContainsString('@page{size:A4 portrait', $source);
            $this->assertStringContainsString('class="watermark"', $source);
            $this->assertStringContainsString("branding['school_name']", $source);
            $this->assertStringContainsString('no editable form fields', $source);
        }
    }

    public function test_pdf_controller_preserves_student_authorization_and_official_tor_leadership_gate(): void
    {
        $source = (string) file_get_contents(app_path('Http/Controllers/AcademicRecordController.php'));

        $this->assertGreaterThanOrEqual(4, substr_count($source, 'StudentAccess::canViewStudent'));
        $this->assertGreaterThanOrEqual(2, substr_count($source, 'StudentAccess::isLeadership'));
        $this->assertStringContainsString("'Cache-Control' => 'private, no-store, max-age=0'", $source);
    }
}
