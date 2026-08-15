<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Support\StudentAccess;
use App\Support\StudentAudit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentDocumentController extends Controller
{
    public function store(Request $request, Student $student): RedirectResponse
    {
        abort_unless(StudentAccess::canManageDocuments($request->user()), 403);
        abort_unless(config('student_portal.documents.allow_local_fallback') === false, 500);

        $data = $request->validate([
            'document_type' => ['required','string','max:80'],
            'provider' => ['required', Rule::in(['google_drive','google_cloud_storage','other_external'])],
            'external_id' => [
                'required','string','max:512',
                Rule::unique('student_documents','external_id')
                    ->where(fn ($query) => $query->where('provider', $request->string('provider')->toString())),
            ],
            'display_name' => ['required','string','max:255'],
            'mime_type' => ['nullable','string','max:120'],
            'size_bytes' => ['nullable','integer','min:0'],
            'classification' => ['required', Rule::in(['confidential','highly_confidential'])],
        ]);

        $document = $student->documents()->create([
            ...$data,
            'registered_by' => $request->user()->id,
        ]);

        StudentAudit::record(
            $request->user(),
            $student,
            'document.registered',
            StudentDocument::class,
            $document,
            ['document_type','provider','external_id','display_name','mime_type','size_bytes','classification'],
            'External confidential document reference registered.'
        );

        return back()->with(
            'success',
            'External document reference registered. No confidential file was copied to the website host.'
        );
    }
}
