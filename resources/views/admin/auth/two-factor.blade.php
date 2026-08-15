<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#071f3d">
    <meta name="robots" content="noindex,nofollow">
    <title>Two-Factor Authentication | NACS-Phil</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/phase39-auth/auth.css') }}">
</head>
<body class="nacs-auth-body">
    <a href="#nacs-auth-main" class="nacs-auth-skip">Skip to verification form</a>

    <main id="nacs-auth-main" class="nacs-auth-shell">
        <section class="nacs-auth-card nacs-auth-card--staff" aria-labelledby="two-factor-title">
            <div class="nacs-auth-branding">
                <div class="nacs-auth-brand" aria-label="NACS-Phil School Administration">
                    <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="84" height="84">
                    <span>
                        <strong>NACS-Phil</strong>
                        <small>School Administration</small>
                    </span>
                </div>
            </div>

            <div class="nacs-auth-copy">
                <p class="nacs-auth-kicker">Secure staff verification</p>
                <h1 id="two-factor-title">Two-Factor Authentication</h1>
                <p class="nacs-auth-lead">
                    Enter the current 6-digit authenticator code or one unused recovery code to continue.
                </p>
            </div>

            @if($errors->any())
                <div class="nacs-auth-alert nacs-auth-alert--error" role="alert">
                    <strong>Verification was not accepted.</strong>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="nacs-auth-form">
                @csrf

                <label class="nacs-auth-field">
                    <span>Authenticator or recovery code</span>
                    <input
                        name="code"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        maxlength="32"
                        required
                        autofocus
                        placeholder="Enter your verification code"
                    >
                </label>

                <button type="submit" class="nacs-auth-primary">Verify &amp; continue</button>
            </form>
        </section>
    </main>
</body>
</html>
