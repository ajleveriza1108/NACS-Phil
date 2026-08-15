@extends('portal.layout', ['title' => 'Portal Sign In'])

@section('content')
<section class="sis-login-card">
    <div class="sis-kicker">Private school portal</div>
    <h1>Student &amp; Parent Sign In</h1>
    <p>Use the school-registered email address for your authorized account.</p>

    <form method="POST" action="{{ route('portal.login.store') }}" class="sis-form">
        @csrf
        <label>
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </label>
        <label>
            <span>Password</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <label class="sis-check">
            <input type="checkbox" name="remember" value="1">
            <span>Keep me signed in on this private device</span>
        </label>
        <button type="submit" class="sis-primary">Sign in securely</button>
    </form>

    <p class="sis-help">Account creation and access changes are controlled by NACS-Phil staff.</p>
</section>
@endsection
