@extends('portal.layout', ['title' => 'Change Password'])

@section('content')
<section class="nacs-auth-card nacs-auth-card--portal" aria-labelledby="portal-password-title">
    <div class="nacs-auth-copy">
        <p class="nacs-auth-kicker">Secure your portal access</p>
        <h1 id="portal-password-title">Change Password</h1>
        <p class="nacs-auth-lead">
            Choose a strong password to continue using your student or parent portal account.
        </p>
        <p class="nacs-auth-note">
            Use at least 12 characters with uppercase and lowercase letters, a number, and a symbol.
        </p>
    </div>

    <form method="POST" action="{{ route('portal.password.update') }}" class="nacs-auth-form">
        @csrf
        @method('PATCH')

        <label class="nacs-auth-field">
            <span>Current password</span>
            <input
                type="password"
                name="current_password"
                autocomplete="current-password"
                required
                placeholder="Enter your current password"
            >
        </label>

        <label class="nacs-auth-field">
            <span>New password</span>
            <input
                type="password"
                name="password"
                autocomplete="new-password"
                required
                placeholder="Create a new password"
            >
        </label>

        <label class="nacs-auth-field">
            <span>Confirm new password</span>
            <input
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                required
                placeholder="Repeat the new password"
            >
        </label>

        <button type="submit" class="nacs-auth-primary">Update password</button>
    </form>

    <div class="nacs-auth-actions">
        <a href="{{ route('portal.dashboard') }}" class="nacs-auth-secondary">Return to dashboard</a>
    </div>
</section>
@endsection
