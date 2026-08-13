@extends('layouts.admissions-portal-phase9c')
@section('title','Preliminary Application')
@section('content')
<section class="adm9-hero"><div class="adm9-shell"><span>Preliminary application</span><h1>Start an admissions application.</h1><p>This is an initial application only. Final requirements, assessment, acceptance, and enrollment remain subject to official school review.</p></div></section>
<section class="adm9-section"><div class="adm9-shell adm9-narrow">
@if($errors->any())<div class="adm9-alert adm9-alert--error" role="alert"><strong>Please check the form.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('admissions.apply.store') }}" class="adm9-card adm9-form">@csrf
<div class="adm9-two">
<label><span>Parent / guardian name *</span><input name="guardian_name" value="{{ old('guardian_name') }}" maxlength="120" required></label>
<label><span>Student name *</span><input name="student_name" value="{{ old('student_name') }}" maxlength="120" required></label>
</div>
<div class="adm9-two">
<label><span>Email</span><input type="email" name="guardian_email" value="{{ old('guardian_email') }}" maxlength="180"><small>Email or phone is required.</small></label>
<label><span>Phone</span><input name="guardian_phone" value="{{ old('guardian_phone') }}" maxlength="40"><small>Email or phone is required.</small></label>
</div>
<div class="adm9-two">
<label><span>Applying for *</span><select name="applying_for_level" required><option value="">Choose level</option>@foreach($levels as $level)<option value="{{ $level }}" @selected(old('applying_for_level')===$level)>{{ $level }}</option>@endforeach</select></label>
<label><span>School year *</span><input name="school_year" value="{{ old('school_year',$schoolYear) }}" maxlength="20" required></label>
</div>
<label><span>Questions or information for Admissions <small>Optional</small></span><textarea name="family_notes" rows="5" maxlength="3000">{{ old('family_notes') }}</textarea></label>
<div class="adm9-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
<label class="adm9-check"><input type="checkbox" name="privacy_consent" value="1" required @checked(old('privacy_consent'))><span>I consent to the school using this information to review and respond to this admissions application. I have read the <a href="{{ route('privacy') }}">privacy information</a>.</span></label>
<label class="adm9-check"><input type="checkbox" name="application_consent" value="1" required @checked(old('application_consent'))><span>I understand this is a preliminary application and does not guarantee acceptance or enrollment.</span></label>
<button class="adm9-button" type="submit">Submit preliminary application &rarr;</button>
</form>
</div></section>
@endsection