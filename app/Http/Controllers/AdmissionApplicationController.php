<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\AdmissionDocument;
use App\Models\AdmissionEvent;
use App\Models\SiteContent;
use App\Support\AdmissionsContent;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdmissionApplicationController extends Controller
{
    public function create(): View
    {
        $content = SiteContent::valuesFor('admissions', AdmissionsContent::defaults());

        return view('admissions.apply', [
            'levels' => AdmissionApplication::LEVELS,
            'schoolYear' => (string) ($content['school_year_value'] ?? ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guardian_name' => ['required','string','max:120'],
            'guardian_email' => ['nullable','required_without:guardian_phone','email:rfc','max:180'],
            'guardian_phone' => ['nullable','required_without:guardian_email','string','max:40'],
            'student_name' => ['required','string','max:120'],
            'applying_for_level' => ['required', Rule::in(AdmissionApplication::LEVELS)],
            'school_year' => ['required','string','max:20'],
            'family_notes' => ['nullable','string','max:3000'],
            'privacy_consent' => ['accepted'],
            'application_consent' => ['accepted'],
            'website' => ['nullable','max:0'],
        ]);

        unset($validated['privacy_consent'],$validated['application_consent'],$validated['website']);

        $accessCode = AdmissionApplication::createAccessCode();

        $application = DB::transaction(function () use ($request,$validated,$accessCode): AdmissionApplication {
            $application = AdmissionApplication::create($validated + [
                'reference_code' => AdmissionApplication::createReferenceCode(),
                'access_code_hash' => Hash::make(AdmissionApplication::normalizeAccessCode($accessCode)),
                'status' => 'submitted',
                'public_status_message' => 'Your preliminary application has been received and is waiting for school review.',
                'privacy_consent_at' => now(),
                'application_consent_at' => now(),
                'submitted_at' => now(),
                'ip_hash' => hash_hmac('sha256',(string)$request->ip(),(string)config('app.key')),
                'user_agent' => mb_substr((string)$request->userAgent(),0,500),
            ]);

            AdmissionEvent::create([
                'admission_application_id' => $application->id,
                'event_type' => 'application_submitted',
                'new_status' => 'submitted',
                'public_message' => 'Preliminary application submitted.',
            ]);

            return $application;
        });

        $request->session()->put('admission_receipt', [
            'reference_code' => $application->reference_code,
            'access_code' => $accessCode,
        ]);

        return redirect()->route('admissions.receipt',$application);
    }

    public function receipt(Request $request, AdmissionApplication $application): View|RedirectResponse
    {
        $receipt = $request->session()->pull('admission_receipt');

        if (! is_array($receipt) || ($receipt['reference_code'] ?? '') !== $application->reference_code) {
            return redirect()->route('admissions.track')->with(
                'status',
                'The one-time receipt has already been shown. Use your saved reference and access code.'
            );
        }

        return view('admissions.receipt', [
            'application' => $application,
            'accessCode' => (string) $receipt['access_code'],
        ]);
    }

    public function track(): View
    {
        return view('admissions.track');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reference_code' => ['required','string','max:32'],
            'access_code' => ['required','string','max:30'],
        ]);

        $reference = Str::upper(trim($validated['reference_code']));
        $application = AdmissionApplication::query()->where('reference_code',$reference)->first();

        if (! $application || ! $application->verifyAccessCode($validated['access_code'])) {
            return back()
                ->withInput(['reference_code'=>$reference])
                ->withErrors(['access_code'=>'The reference or access code is incorrect.']);
        }

        $request->session()->put('admission_portal', [
            'reference_code' => $application->reference_code,
            'verified_at' => time(),
        ]);

        return redirect()->route('admissions.status',$application);
    }

    public function show(AdmissionApplication $application): View
    {
        $application->load([
            'documents' => fn ($query) => $query->latest(),
            'events' => fn ($query) => $query->whereNotNull('public_message')->oldest(),
        ]);

        return view('admissions.status', [
            'application' => $application,
            'documentTypes' => AdmissionDocument::TYPES,
            'canUploadDocuments' => $application->status === 'awaiting_documents',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admission_portal');

        return redirect()->route('admissions.track')->with('status','Admissions status access was closed.');
    }

    public function uploadDocument(Request $request, AdmissionApplication $application): RedirectResponse
    {
        abort_unless($application->status === 'awaiting_documents',403,'Document uploads are available only when the school requests documents.');

        $validated = $request->validate([
            'document_type' => ['required', Rule::in(array_keys(AdmissionDocument::TYPES))],
            'document' => ['required','file','mimes:pdf,jpg,jpeg,png','max:5120'],
            'document_consent' => ['accepted'],
        ]);

        $file = $validated['document'];
        $extension = Str::lower($file->getClientOriginalExtension() ?: 'bin');
        $storedName = (string) Str::uuid().'.'.$extension;
        $path = $application->id.'/'.$storedName;

        $this->disk()->putFileAs(
            (string) $application->id,
            $file,
            $storedName
        );

        $document = AdmissionDocument::create([
            'admission_application_id' => $application->id,
            'document_type' => $validated['document_type'],
            'original_name' => mb_substr($file->getClientOriginalName(),0,255),
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => (int) $file->getSize(),
            'uploaded_by' => 'applicant',
        ]);

        AdmissionEvent::create([
            'admission_application_id' => $application->id,
            'event_type' => 'document_uploaded',
            'public_message' => 'A requested document was uploaded: '.$document->typeLabel().'.',
            'metadata' => ['document_id'=>$document->id],
        ]);

        return back()->with('success','Document uploaded privately for school review.');
    }

    public function destroyDocument(AdmissionApplication $application, AdmissionDocument $document): RedirectResponse
    {
        abort_unless($document->admission_application_id === $application->id,404);
        abort_if($document->is_verified || $document->uploaded_by !== 'applicant',403);

        $this->disk()->delete($document->path);
        $label = $document->typeLabel();
        $document->delete();

        AdmissionEvent::create([
            'admission_application_id' => $application->id,
            'event_type' => 'document_removed',
            'public_message' => 'An unverified uploaded document was removed: '.$label.'.',
        ]);

        return back()->with('success','The unverified document was removed.');
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/private/admissions'),
            'throw' => true,
        ]);
    }
}