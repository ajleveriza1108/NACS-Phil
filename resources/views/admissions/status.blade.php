@extends('layouts.site-current', ['bodyClass' => 'adm9-body nacs-current-page nacs-current-page--admissions-portal', 'mainId' => 'main', 'mainClass' => 'adm9-main', 'assetBundle' => 'admissions-portal', 'useVite' => false])
@section('title','Application Status')
@section('content')
<section class="adm9-section"><div class="adm9-shell">
<div class="adm9-status-head">
<div><span class="adm9-kicker">Application {{ $application->reference_code }}</span><h1>{{ $application->student_name }}</h1><p>{{ $application->applying_for_level }} &middot; {{ $application->school_year }}</p></div>
<form method="POST" action="{{ route('admissions.track.logout') }}">@csrf<button class="adm9-link-button">Close private access</button></form>
</div>
@if(session('success'))<div class="adm9-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="adm9-alert adm9-alert--error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="adm9-status-grid">
<section class="adm9-card">
<span class="adm9-kicker">Current status</span><h2>{{ $application->statusLabel() }}</h2><p>{{ $application->public_status_message ?: 'No additional school message yet.' }}</p>
</section>
<section class="adm9-card">
<span class="adm9-kicker">Timeline</span>
<div class="adm9-timeline">@forelse($application->events as $event)@if($event->public_message)<article><span></span><div><strong>{{ $event->public_message }}</strong><small>{{ $event->created_at->format('M j, Y g:i A') }}</small></div></article>@endif @empty<p>No updates yet.</p>@endforelse</div>
</section>
<section class="adm9-card adm9-docs">
<span class="adm9-kicker">Private documents</span>
@if($canUploadDocuments)
<h2>The school requested documents.</h2><p>Upload only the document specifically requested in the school status message.</p>
<form method="POST" enctype="multipart/form-data" action="{{ route('admissions.documents.store',$application) }}" class="adm9-form">@csrf
<label><span>Document type</span><select name="document_type" required>@foreach($documentTypes as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
<label><span>File</span><input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" required><small>PDF/JPG/PNG, maximum 5 MB.</small></label>
<label class="adm9-check"><input type="checkbox" name="document_consent" value="1" required><span>I confirm the school requested this document and I consent to its private admissions review.</span></label>
<button class="adm9-button" type="submit">Upload requested document</button>
</form>
@else
<h2>No document upload requested.</h2><p>For privacy, document upload remains unavailable until an authorized school reviewer changes the status to &ldquo;Awaiting documents.&rdquo;</p>
@endif
@if($application->documents->isNotEmpty())<div class="adm9-document-list">@foreach($application->documents as $document)<article><div><strong>{{ $document->typeLabel() }}</strong><small>{{ $document->original_name }} &middot; {{ $document->formattedSize() }}</small></div><span>{{ $document->is_verified ? 'Verified' : 'Pending review' }}</span>@if(!$document->is_verified && $document->uploaded_by==='applicant')<form method="POST" action="{{ route('admissions.documents.destroy',[$application,$document]) }}">@csrf @method('DELETE')<button type="submit" class="adm9-text-danger">Remove</button></form>@endif</article>@endforeach</div>@endif
</section>
</div>
</div></section>
@endsection
