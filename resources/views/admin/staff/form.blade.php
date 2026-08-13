@extends('admin.layouts.app', ['title' => isset($staff) ? 'Edit Staff Account' : 'Add Staff Account'])

@section('content')
@php($editing = isset($staff))
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.staff.index') }}">&larr; Staff Accounts</a>
        <span class="cm-eyebrow">Super Admin only</span>
        <h1>{{ $editing ? 'Edit Staff Account' : 'Add Staff Account' }}</h1>
        <p>{{ $editing ? 'Update this staff memberâ€™s role, status, or login details.' : 'Create access for a Principal or Teacher. No additional Super Admin is created here.' }}</p>
    </div>
</section>

<form method="POST" action="{{ $editing ? route('admin.staff.update',$staff) : route('admin.staff.store') }}" class="cm-editor p9-staff-form">
    @csrf
    @if($editing) @method('PATCH') @endif

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>Staff identity</h2><p>Use the staff memberâ€™s official school contact information.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Name</span><input name="name" value="{{ old('name',$staff->name ?? '') }}" maxlength="100" required></label>
            <label class="cm-field"><span>Email</span><input type="email" name="email" value="{{ old('email',$staff->email ?? '') }}" maxlength="150" required></label>
            <label class="cm-field"><span>Role</span>
                <select name="role" required>
                    <option value="principal" @selected(old('role',$staff->role ?? '') === 'principal')>Principal / School Admin</option>
                    <option value="teacher" @selected(old('role',$staff->role ?? 'teacher') === 'teacher')>Teacher / Content Editor</option>
                </select>
            </label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>2</span><div><h2>Password</h2><p>Minimum 12 characters with uppercase/lowercase letters and numbers.</p></div></div>
        <div class="cm-fields cm-two">
            <label class="cm-field"><span>{{ $editing ? 'New password (optional)' : 'Password' }}</span><input type="password" name="password" {{ $editing ? '' : 'required' }} autocomplete="new-password"></label>
            <label class="cm-field"><span>Confirm password</span><input type="password" name="password_confirmation" {{ $editing ? '' : 'required' }} autocomplete="new-password"></label>
        </div>
    </section>

    @if($editing)
    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>3</span><div><h2>Account status</h2><p>Deactivating an account blocks admin access without deleting its history.</p></div></div>
        <div class="cm-fields">
            <label class="p9-toggle-line"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$staff->is_active))><span><strong>Account is active</strong><small>Turn this off when the staff member should no longer sign in.</small></span></label>
        </div>
    </section>
    @endif

    <div class="cm-save-bar">
        <div><strong>{{ $editing ? 'Save staff changes?' : 'Create staff account?' }}</strong><small>The Super Admin account is not modified by this form.</small></div>
        <button type="submit" class="cm-button cm-button--primary">{{ $editing ? 'Save Staff Account' : 'Create Staff Account' }}</button>
    </div>
</form>
@endsection