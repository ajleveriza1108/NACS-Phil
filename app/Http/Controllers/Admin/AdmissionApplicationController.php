<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\AdmissionDocument;
use App\Models\AdmissionEvent;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdmissionApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $query = AdmissionApplication::query()
            ->withCount('documents')
            ->search($search)
            ->latest('submitted_at');

        if (array_key_exists($status,AdmissionApplication::STATUSES)) {
            $query->where('status',$status);
        }

        return view('admin.admissions.index',[
            'applications'=>$query->paginate(20)->withQueryString(),
            'statuses'=>AdmissionApplication::STATUSES,
            'status'=>$status,
            'search'=>$search,
        ]);
    }

    public function show(AdmissionApplication $application): View
    {
        $application->load([
            'documents'=>fn($query)=>$query->with('verifiedBy')->latest(),
            'events'=>fn($query)=>$query->with('actor')->latest(),
        ]);

        return view('admin.admissions.show',[
            'application'=>$application,
            'statuses'=>AdmissionApplication::STATUSES,
        ]);
    }

    public function update(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $validated = $request->validate([
            'status'=>['required',Rule::in(array_keys(AdmissionApplication::STATUSES))],
            'public_status_message'=>['nullable','string','max:5000'],
            'admin_notes'=>['nullable','string','max:20000'],
        ]);

        $oldStatus=$application->status;
        $oldMessage=(string)$application->public_status_message;

        DB::transaction(function() use($request,$application,$validated,$oldStatus,$oldMessage): void {
            $application->update($validated);

            if($oldStatus !== $application->status || $oldMessage !== (string)$application->public_status_message){
                AdmissionEvent::create([
                    'admission_application_id'=>$application->id,
                    'actor_user_id'=>$request->user()?->id,
                    'event_type'=>$oldStatus !== $application->status ? 'status_changed' : 'public_message_updated',
                    'old_status'=>$oldStatus,
                    'new_status'=>$application->status,
                    'public_message'=>$application->public_status_message,
                ]);
            }
        });

        return back()->with('success','Admissions application updated.');
    }

    public function verifyDocument(Request $request, AdmissionApplication $application, AdmissionDocument $document): RedirectResponse
    {
        abort_unless($document->admission_application_id === $application->id,404);

        $validated=$request->validate([
            'is_verified'=>['required','boolean'],
            'admin_notes'=>['nullable','string','max:5000'],
        ]);

        $verified=(bool)$validated['is_verified'];

        $document->update([
            'is_verified'=>$verified,
            'verified_at'=>$verified ? now() : null,
            'verified_by_user_id'=>$verified ? $request->user()?->id : null,
            'admin_notes'=>$validated['admin_notes'] ?? null,
        ]);

        AdmissionEvent::create([
            'admission_application_id'=>$application->id,
            'actor_user_id'=>$request->user()?->id,
            'event_type'=>$verified ? 'document_verified' : 'document_verification_removed',
            'public_message'=>$verified
                ? 'The school verified a submitted document: '.$document->typeLabel().'.'
                : 'The school requested another review of a submitted document: '.$document->typeLabel().'.',
            'metadata'=>['document_id'=>$document->id],
        ]);

        return back()->with('success','Document review updated.');
    }

    public function downloadDocument(AdmissionApplication $application, AdmissionDocument $document): StreamedResponse
    {
        abort_unless($document->admission_application_id === $application->id,404);
        abort_unless($this->disk()->exists($document->path),404);

        return $this->disk()->download(
            $document->path,
            $document->original_name,
            ['Content-Type'=>$document->mime_type ?: 'application/octet-stream']
        );
    }

    public function rotateAccessCode(Request $request, AdmissionApplication $application): RedirectResponse
    {
        $accessCode=AdmissionApplication::createAccessCode();
        $application->replaceAccessCode($accessCode);

        AdmissionEvent::create([
            'admission_application_id'=>$application->id,
            'actor_user_id'=>$request->user()?->id,
            'event_type'=>'access_code_rotated',
            'public_message'=>'A new private access code was issued for this application.',
        ]);

        return back()
            ->with('success','A new access code was generated. Copy it now; it will not be shown again.')
            ->with('new_access_code',$accessCode);
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::build([
            'driver'=>'local',
            'root'=>storage_path('app/private/admissions'),
            'throw'=>true,
        ]);
    }
}