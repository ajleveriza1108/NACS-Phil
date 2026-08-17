@extends('layouts.site-current', ['bodyClass' => 'adm9-body nacs-current-page nacs-current-page--admissions-portal', 'mainId' => 'main', 'mainClass' => 'adm9-main', 'assetBundle' => 'admissions-portal', 'useVite' => false])
@section('title','Application Receipt')
@section('content')
<section class="adm9-section"><div class="adm9-shell adm9-narrow">
<div class="adm9-card adm9-receipt">
<span class="adm9-success">Application received</span>
<h1>Save these two private codes now.</h1>
<p>This access code is shown only on this receipt. Keep both values private.</p>
<div class="adm9-code"><small>Reference</small><strong>{{ $application->reference_code }}</strong></div>
<div class="adm9-code"><small>Access code</small><strong>{{ $accessCode }}</strong></div>
<div class="adm9-warning"><strong>Important</strong><p>If you lose the access code, authorized school staff can issue a new one after verifying the request.</p></div>
<a class="adm9-button" href="{{ route('admissions.track') }}">Go to application tracking &rarr;</a>
</div>
</div></section>
@endsection
