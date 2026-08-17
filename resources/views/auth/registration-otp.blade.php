@extends('layouts.site-current', ['title' => 'Verify Registration Email', 'bodyClass' => 'nacs11-public nacs-current-page nacs-current-page--public', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'public', 'useVite' => true])
@section('content')
<section class="nacs46-registration">
    <div class="nacs11-shell nacs46-registration__shell">
        <div class="nacs46-registration__card">
            <span class="nacs46-registration__eyebrow">Final registration step</span>
            <h1>Verify your email.</h1>
            <p class="nacs46-registration__lead">Enter the 6-digit code sent to <strong>{{ $maskedEmail }}</strong>. The account remains inactive until verification succeeds.</p>

            @if(session('success'))
                <div class="nacs46-registration__success" role="status">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="nacs46-registration__alert" role="alert">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('registration.otp.verify', ['token' => $registrationToken]) }}" class="nacs46-registration__form">
                @csrf
                <label><span>6-digit verification code</span><input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" required pattern="[0-9]{6}" maxlength="6" class="nacs46-registration__otp"></label>
                <button type="submit" class="nacs11-button nacs11-button--primary nacs46-registration__submit">Verify and activate account</button>
            </form>

            <form method="POST" action="{{ route('registration.otp.resend', ['token' => $registrationToken]) }}" class="nacs46-registration__resend">
                @csrf
                <button type="submit">Resend verification code</button>
            </form>

            <p class="nacs46-registration__privacy">Codes expire after {{ (int) config('registration.otp_minutes', 10) }} minutes. After {{ (int) config('registration.otp_max_attempts', 5) }} unsuccessful attempts, request a new code.</p>
        </div>
    </div>
</section>
@endsection
