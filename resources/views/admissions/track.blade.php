@extends('layouts.admissions-portal-phase9c')
@section('title','Track Application')
@section('content')
<section class="adm9-hero"><div class="adm9-shell"><span>Private tracking</span><h1>View your application status.</h1><p>Enter the reference and access code from your one-time receipt.</p></div></section>
<section class="adm9-section"><div class="adm9-shell adm9-narrow">
@if(session('status'))<div class="adm9-alert">{{ session('status') }}</div>@endif
@if($errors->any())<div class="adm9-alert adm9-alert--error" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('admissions.track.authenticate') }}" class="adm9-card adm9-form">@csrf
<label><span>Application reference *</span><input name="reference_code" value="{{ old('reference_code') }}" maxlength="32" autocomplete="off" required></label>
<label><span>Private access code *</span><input name="access_code" maxlength="30" autocomplete="off" required></label>
@include('partials.turnstile', ['action' => 'admissions_track'])
<button class="adm9-button" type="submit">Open application status &rarr;</button>
</form>
</div></section>
@endsection