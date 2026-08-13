@extends('admin.layouts.app', ['title' => 'Login & Security'])
@section('content')
@if(session('new_recovery_codes'))
<div class="cm-alert cm-alert--success"><strong>Save these recovery codes now. Each code works once:</strong><div class="p12-secret">{{ implode('  ', session('new_recovery_codes')) }}</div></div>
@endif
<section class="cm-page-head"><div><span class="cm-eyebrow">My Account</span><h1>Login &amp; Security</h1><p>Manage password, two-factor authentication, and other signed-in sessions.</p></div></section>
<div class="p12-grid">
<section class="p12-card"><h2>Password</h2><p>Use at least 12 characters with upper/lowercase letters and a number.</p>
<form method="POST" action="{{ route('admin.security.password') }}" class="cm-compose">@csrf @method('PATCH')
<label class="cm-field"><span>Current Password</span><input type="password" name="current_password" required></label>
<label class="cm-field"><span>New Password</span><input type="password" name="password" required></label>
<label class="cm-field"><span>Confirm New Password</span><input type="password" name="password_confirmation" required></label>
<button class="cm-button cm-button--primary">Change Password</button></form></section>

<section class="p12-card"><h2>Two-Factor Authentication</h2>
@if($user->twoFactorEnabled())
<p><span class="p12-badge p12-badge--good">Enabled</span> Your account requires an authenticator code after the password.</p>
<form method="POST" action="{{ route('admin.security.two-factor.disable') }}" class="cm-compose">@csrf @method('DELETE')
<label class="cm-field"><span>Current Password</span><input type="password" name="current_password" required></label>
<label class="cm-field"><span>Authenticator Code</span><input name="code" inputmode="numeric" maxlength="6" required></label>
<button class="cm-button cm-button--secondary">Disable 2FA</button></form>
@elseif($pendingSecret)
<p>Add this secret manually in a TOTP authenticator app. QR-code generation is intentionally not dependent on an external service.</p>
<div class="p12-secret">{{ $pendingSecret }}</div>
<p class="p12-secret">{{ $provisioningUri }}</p>
<form method="POST" action="{{ route('admin.security.two-factor.confirm') }}" class="cm-compose">@csrf
<label class="cm-field"><span>6-digit Code</span><input name="code" inputmode="numeric" maxlength="6" required></label>
<button class="cm-button cm-button--primary">Confirm 2FA</button></form>
@else
<p>Leadership accounts should enable 2FA before public deployment.</p>
<form method="POST" action="{{ route('admin.security.two-factor.setup') }}" class="cm-compose">@csrf
<label class="cm-field"><span>Current Password</span><input type="password" name="current_password" required></label>
<button class="cm-button cm-button--primary">Start 2FA Setup</button></form>
@endif
</section>

<section class="p12-card"><h2>Recent Security</h2><p>Last login: {{ $user->last_login_at?->format('M j, Y g:i A') ?: 'Not recorded yet' }}</p><p>Failed attempts: {{ $user->failed_login_count }}</p><p>Temporary lock: {{ $user->locked_until?->isFuture() ? $user->locked_until->diffForHumans() : 'No' }}</p></section>
<section class="p12-card"><h2>Other Sessions</h2><p>When using database sessions, revoke other signed-in browsers and devices without ending this session.</p><form method="POST" action="{{ route('admin.security.revoke-sessions') }}">@csrf<label class="cm-field"><span>Current Password</span><input type="password" name="current_password" required></label><button class="cm-button cm-button--secondary">Revoke Other Sessions</button></form></section>
</div>
@endsection
