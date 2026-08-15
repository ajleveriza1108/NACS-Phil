@extends('portal.layout', ['title' => 'Portal Sign In'])

@section('content')
<section class="nacs-auth-card nacs-auth-card--portal" aria-labelledby="portal-login-title">
    <div class="nacs-auth-copy">
        <p class="nacs-auth-kicker">Private portal access</p>
        <h1 id="portal-login-title">Student &amp; Parent Sign In</h1>
        <p class="nacs-auth-lead">
            Use the school-registered email address for your authorized student or parent account.
        </p>
        <p class="nacs-auth-note">
            Account creation and access updates are managed by NACS-Phil staff.
        </p>
    </div>

    <form method="POST" action="{{ route('portal.login.store') }}" class="nacs-auth-form">
        @csrf

        <label class="nacs-auth-field">
            <span>Email address</span>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
                placeholder="student_or_parent@nacsphil.example"
            >
        </label>

        <label class="nacs-auth-field">
            <span>Password</span>
            <input
                type="password"
                name="password"
                autocomplete="current-password"
                required
                placeholder="Enter your password"
            >
        </label>

        <label class="nacs-auth-check">
            <input type="checkbox" name="remember" value="1">
            <span>Keep me signed in on this private device</span>
        </label>

        <button type="submit" class="nacs-auth-primary">Sign in securely</button>
    </form>

    <div class="nacs-auth-actions">
        <a href="{{ route('home') }}" class="nacs-auth-secondary">Return to public website</a>
    </div>
</section>
@endsection
