@extends('portal.layout', ['title' => 'Change Password'])

@section('content')
<section class="sis-login-card">
    <div class="sis-kicker">Account security</div>
    <h1>Choose your private password</h1>
    <p>Your temporary password must be replaced before student records can be opened.</p>

    <form method="POST" action="{{ route('portal.password.update') }}" class="sis-form">
        @csrf
        @method('PATCH')
        <label>
            <span>Current temporary password</span>
            <input type="password" name="current_password" autocomplete="current-password" required>
        </label>
        <label>
            <span>New password</span>
            <input type="password" name="password" autocomplete="new-password" required>
        </label>
        <label>
            <span>Confirm new password</span>
            <input type="password" name="password_confirmation" autocomplete="new-password" required>
        </label>
        <button type="submit" class="sis-primary">Save new password</button>
    </form>
</section>
@endsection
