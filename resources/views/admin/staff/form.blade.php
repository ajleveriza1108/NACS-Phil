@php($editing = isset($staff))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Staff Account' : 'Invite Staff Account'])
@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.staff.index') }}">&larr; Staff Accounts</a><h1>{{ $editing ? 'Edit Staff Account' : 'Invite Staff Account' }}</h1><p>{{ $editing ? 'Update authorized staff access.' : 'Create an inactive staff account and send a secure registration invitation to the approved email.' }}</p></div></section>
<form method="POST" action="{{ $editing ? route('admin.staff.update',$staff) : route('admin.staff.store') }}" class="cm-compose">@csrf @if($editing) @method('PATCH') @endif
<div class="cm-two"><label class="cm-field"><span>Name</span><input name="name" required maxlength="100" value="{{ old('name',$staff->name ?? '') }}"></label><label class="cm-field"><span>Email</span><input type="email" name="email" required maxlength="150" value="{{ old('email',$staff->email ?? '') }}"></label></div>
<label class="cm-field"><span>Role</span><select name="role"><option value="principal" @selected(old('role',$staff->role ?? 'teacher')==='principal')>Principal / School Admin</option><option value="teacher" @selected(old('role',$staff->role ?? 'teacher')==='teacher')>Teacher / Content Editor</option></select></label>
@if($editing)
<div class="cm-two"><label class="cm-field"><span>New Password (optional emergency reset)</span><input type="password" name="password" minlength="12" maxlength="128" autocomplete="new-password"><small>12+ characters with uppercase, lowercase, number, and symbol.</small></label><label class="cm-field"><span>Confirm Password</span><input type="password" name="password_confirmation" minlength="12" maxlength="128" autocomplete="new-password"></label></div>
<div class="cm-publish-box"><label class="cm-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$staff->is_active))><span><strong>Account active</strong></span></label><label class="cm-check"><input type="checkbox" name="force_password_reset" value="1" @checked(old('force_password_reset',$staff->force_password_reset ?? false))><span><strong>Require password change</strong></span></label></div>
@else
<div class="cm-publish-box"><div><strong>Secure registration</strong><small>The teacher creates their own strong password from the invitation. A 6-digit OTP is sent to the same email and must be verified before activation.</small></div></div>
@endif
<button class="cm-button cm-button--primary">{{ $editing ? 'Save Staff Account' : 'Send Registration Invitation' }}</button>
</form>
@endsection
