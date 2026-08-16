@php($editing = isset($staff))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Staff Account' : 'Invite Staff Account'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.staff.index') }}">&larr; Staff Accounts</a>
        <h1>{{ $editing ? 'Edit Staff Account' : 'Invite Privileged Staff Account' }}</h1>
        <p>{{ $editing ? 'Update this authorized staff member within the approved role model.' : 'Create an inactive account. The staff member chooses their own strong password and completes email OTP verification before activation.' }}</p>
    </div>
</section>

@if($officialEmailDomain)
    <section class="cm-panel">
        <strong>Official staff email policy active</strong>
        <p>New or edited privileged staff accounts must use <strong>@{{ $officialEmailDomain }}</strong>.</p>
    </section>
@else
    <section class="cm-panel">
        <strong>Official staff email domain not enforced yet</strong>
        <p>Personal/test addresses remain allowed while the school domain is pending. After the official domain is ready, set <code>NACS_SCHOOL_EMAIL_DOMAIN=nacsphil.edu.ph</code> and clear the Laravel config cache.</p>
    </section>
@endif

<form method="POST" action="{{ $editing ? route('admin.staff.update',$staff) : route('admin.staff.store') }}" class="cm-compose">
    @csrf
    @if($editing) @method('PATCH') @endif

    <div class="cm-two">
        <label class="cm-field"><span>Name</span><input name="name" required maxlength="100" value="{{ old('name',$staff->name ?? '') }}"></label>
        <label class="cm-field"><span>Email</span><input type="email" name="email" required maxlength="150" value="{{ old('email',$staff->email ?? '') }}"></label>
    </div>

    <label class="cm-field">
        <span>Role</span>
        <select name="role" required>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role',$staff->role ?? 'teacher') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <small>Choose the narrowest role that matches the staff member's real responsibility.</small>
    </label>

    <section class="cm-panel">
        <strong>Role boundaries</strong>
        <div class="p13-list">
            @foreach($roleDescriptions as $value => $description)
                <div><span><strong>{{ $roles[$value] }}</strong><small>{{ $description }}</small></span></div>
            @endforeach
        </div>
    </section>

    @if($editing)
        <div class="cm-two">
            <label class="cm-field"><span>New Password (optional emergency reset)</span><input type="password" name="password" minlength="12" maxlength="128" autocomplete="new-password"><small>12+ characters with uppercase, lowercase, number, and symbol.</small></label>
            <label class="cm-field"><span>Confirm Password</span><input type="password" name="password_confirmation" minlength="12" maxlength="128" autocomplete="new-password"></label>
        </div>
        <div class="cm-publish-box">
            <label class="cm-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$staff->is_active))><span><strong>Account active</strong></span></label>
            <label class="cm-check"><input type="checkbox" name="force_password_reset" value="1" @checked(old('force_password_reset',$staff->force_password_reset ?? false))><span><strong>Require password change</strong></span></label>
        </div>
    @else
        <div class="cm-publish-box">
            <div><strong>Secure registration</strong><small>No temporary password is created by the administrator. The invitee creates a strong password, receives the final 6-digit OTP at the same registered email, and remains inactive until verification succeeds.</small></div>
        </div>
    @endif

    <button class="cm-button cm-button--primary">{{ $editing ? 'Save Staff Account' : 'Send Registration Invitation' }}</button>
</form>
@endsection
