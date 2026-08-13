@php($editing = isset($staff))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Staff Account' : 'Add Staff Account'])
@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.staff.index') }}">&larr; Staff Accounts</a><h1>{{ $editing ? 'Edit Staff Account' : 'Add Staff Account' }}</h1></div></section>
<form method="POST" action="{{ $editing ? route('admin.staff.update',$staff) : route('admin.staff.store') }}" class="cm-compose">@csrf @if($editing) @method('PATCH') @endif
<div class="cm-two"><label class="cm-field"><span>Name</span><input name="name" required maxlength="100" value="{{ old('name',$staff->name ?? '') }}"></label><label class="cm-field"><span>Email</span><input type="email" name="email" required maxlength="150" value="{{ old('email',$staff->email ?? '') }}"></label></div>
<label class="cm-field"><span>Role</span><select name="role"><option value="principal" @selected(old('role',$staff->role ?? 'teacher')==='principal')>Principal / School Admin</option><option value="teacher" @selected(old('role',$staff->role ?? 'teacher')==='teacher')>Teacher / Content Editor</option></select></label>
<div class="cm-two"><label class="cm-field"><span>{{ $editing ? 'New Password (optional)' : 'Temporary Password' }}</span><input type="password" name="password" {{ $editing ? '' : 'required' }}></label><label class="cm-field"><span>Confirm Password</span><input type="password" name="password_confirmation" {{ $editing ? '' : 'required' }}></label></div>
<div class="cm-publish-box">@if($editing)<label class="cm-check"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$staff->is_active))><span><strong>Account active</strong></span></label>@endif<label class="cm-check"><input type="checkbox" name="force_password_reset" value="1" @checked(old('force_password_reset',$staff->force_password_reset ?? true))><span><strong>Require password change</strong><small>Recommended after issuing a temporary password.</small></span></label></div>
<button class="cm-button cm-button--primary">Save Staff Account</button>
</form>
@endsection
