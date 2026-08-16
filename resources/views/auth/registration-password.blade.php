@extends('layouts.site-current', ['title' => 'Complete Account Registration', 'bodyClass' => 'nacs11-public nacs-current-page nacs-current-page--public', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'public', 'useVite' => true])
@section('content')
<section class="nacs46-registration">
    <div class="nacs11-shell nacs46-registration__shell">
        <div class="nacs46-registration__card">
            <span class="nacs46-registration__eyebrow">Secure account registration</span>
            <h1>Create your password.</h1>
            <p class="nacs46-registration__lead">This secure invitation is tied to <strong>{{ $maskedEmail }}</strong>. Your role and email cannot be changed from this page.</p>

            @if($errors->any())
                <div class="nacs46-registration__alert" role="alert">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('registration.password.store', ['token' => $registrationToken]) }}" class="nacs46-registration__form">
                @csrf
                <label><span>Strong password</span><input type="password" name="password" required minlength="12" maxlength="128" autocomplete="new-password"></label>
                <label><span>Confirm password</span><input type="password" name="password_confirmation" required minlength="12" maxlength="128" autocomplete="new-password"></label>

                <div class="nacs46-registration__requirements">
                    <strong>Password requirements</strong>
                    <ul>
                        <li>12 to 128 characters</li>
                        <li>Uppercase and lowercase letters</li>
                        <li>At least one number and one symbol</li>
                        <li>Must not contain your name, email, or student number</li>
                    </ul>
                </div>

                <button type="submit" class="nacs11-button nacs11-button--primary nacs46-registration__submit">Continue and send email OTP</button>
            </form>

            <p class="nacs46-registration__privacy">The account remains inactive until the final 6-digit email code is verified.</p>
        </div>
    </div>
</section>
@endsection
