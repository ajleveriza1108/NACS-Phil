<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Support\AcademicPdfService;
use App\Support\AcademicRecordBuilder;
use App\Support\SchoolDocumentBranding;
use App\Support\StudentAccess;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AcademicRecordController extends Controller
{
    public function reportCard(Request $request, Student $student): View
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $this->loadReportCard($student);

        return view('academic.report-card', [
            'student' => $student,
            'record' => AcademicRecordBuilder::reportCard($student),
            'generatedBy' => $request->user(),
            'branding' => SchoolDocumentBranding::data(),
        ]);
    }

    public function transcript(Request $request, Student $student): View
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $official = $request->boolean('official');

        if ($official) {
            abort_unless(StudentAccess::isLeadership($request->user()), 403);
        }

        $this->loadTranscript($student);

        return view('academic.transcript', [
            'student' => $student,
            'years' => AcademicRecordBuilder::transcript($student),
            'generatedBy' => $request->user(),
            'official' => $official,
            'branding' => SchoolDocumentBranding::data(),
        ]);
    }

    public function reportCardPdf(Request $request, Student $student, AcademicPdfService $pdf): Response
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $this->loadReportCard($student);

        $html = view('academic.pdf.report-card', [
            'student' => $student,
            'record' => AcademicRecordBuilder::reportCard($student),
            'generatedBy' => $request->user(),
            'branding' => SchoolDocumentBranding::data(),
        ])->render();

        return $this->pdfResponse(
            $pdf->render($html),
            'NACS-Report-Card-'.Str::slug($student->fullName()).'.pdf'
        );
    }

    public function transcriptPdf(Request $request, Student $student, AcademicPdfService $pdf): Response
    {
        abort_unless(StudentAccess::canViewStudent($request->user(), $student), 403);

        $official = $request->boolean('official');

        if ($official) {
            abort_unless(StudentAccess::isLeadership($request->user()), 403);
        }

        $this->loadTranscript($student);

        $html = view('academic.pdf.transcript', [
            'student' => $student,
            'years' => AcademicRecordBuilder::transcript($student),
            'generatedBy' => $request->user(),
            'official' => $official,
            'branding' => SchoolDocumentBranding::data(),
        ])->render();

        $prefix = $official ? 'NACS-Official-TOR-' : 'NACS-Draft-TOR-';

        return $this->pdfResponse(
            $pdf->render($html),
            $prefix.Str::slug($student->fullName()).'.pdf'
        );
    }

    private function loadReportCard(Student $student): void
    {
        $student->load([
            'grades.teacher:id,name',
            'attendances',
            'assignments' => fn ($query) => $query
                ->where('status', 'active')
                ->with('teacher:id,name,email'),
        ]);
    }

    private function loadTranscript(Student $student): void
    {
        $student->load([
            'grades.teacher:id,name',
            'assignments' => fn ($query) => $query
                ->where('status', 'active')
                ->with('teacher:id,name,email'),
        ]);
    }

    private function pdfResponse(string $content, string $filename): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
