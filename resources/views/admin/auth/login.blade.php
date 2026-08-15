<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#071f3d">
    <meta name="robots" content="noindex,nofollow">
    <title>Staff Sign In | NACS-Phil</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/phase39-auth/auth.css') }}">
</head>
<body class="nacs-auth-body">
    <a href="#nacs-auth-main" class="nacs-auth-skip">Skip to sign in form</a>

    <main id="nacs-auth-main" class="nacs-auth-shell">
        <section class="nacs-auth-card nacs-auth-card--staff" aria-labelledby="staff-login-title">
            <div class="nacs-auth-branding">
                <a href="{{ route('home') }}" class="nacs-auth-brand" aria-label="Return to the NACS-Phil public website">
                    <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="84" height="84">
                    <span>
                        <strong>NACS-Phil</strong>
                        <small>School Administration</small>
                    </span>
                </a>
            </div>

            <div class="nacs-auth-copy">
                <p class="nacs-auth-kicker">Authorized staff only</p>
                <h1 id="staff-login-title">NACS-Phil Administration</h1>
                <p class="nacs-auth-lead">
                    Sign in with the official administrator or school staff account created and managed by NACS-Phil.
                </p>
                <p class="nacs-auth-note">
                    Initial administrator access is created through <strong>CREATE-ADMIN.bat</strong>.
                </p>
            </div>

            @if ($errors->any())
                <div class="nacs-auth-alert nacs-auth-alert--error" role="alert">
                    <strong>We could not sign you in.</strong>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="nacs-auth-form">
                @csrf

                <label class="nacs-auth-field">
                    <span>Email address</span>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@nacsphil.example"
                    >
                </label>

                <label class="nacs-auth-field">
                    <span>Password</span>
                    <input
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                </label>

                <label class="nacs-auth-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me signed in on this private computer</span>
                </label>

                @include('partials.turnstile', ['action' => 'admin_login'])

                <button type="submit" class="nacs-auth-primary">Sign in securely</button>
            </form>

            <div class="nacs-auth-actions">
                <a href="{{ route('home') }}" class="nacs-auth-secondary">Return to public website</a>
            </div>
        </section>
    </main>
</body>
</html>
