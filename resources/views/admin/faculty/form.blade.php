@php($editing = isset($profile))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Faculty Profile' : 'Add Faculty Profile'])

@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.faculty.index') }}">&larr; Faculty &amp; Staff</a><span class="cm-eyebrow">Public directory</span><h1>{{ $editing ? 'Edit Profile' : 'Add Profile' }}</h1></div></section>
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.faculty.update',$profile) : route('admin.faculty.store') }}" class="cm-compose">
@csrf @if($editing) @method('PUT') @endif
<div class="cm-two">
<label class="cm-field"><span>Name</span><input name="name" value="{{ old('name',$profile->name ?? '') }}" required maxlength="180"></label>
<label class="cm-field"><span>Position / Title</span><input name="position" value="{{ old('position',$profile->position ?? '') }}" required maxlength="180"></label>
<label class="cm-field"><span>Department</span><input name="department" value="{{ old('department',$profile->department ?? '') }}" maxlength="120" placeholder="School Leadership, Elementary Faculty..."></label>
<label class="cm-field"><span>Grade / Subject</span><input name="grade_subject" value="{{ old('grade_subject',$profile->grade_subject ?? '') }}" maxlength="180"></label>
</div>
<label class="cm-field"><span>Short Biography</span><textarea name="biography" rows="6">{{ old('biography',$profile->biography ?? '') }}</textarea></label>
<label class="cm-field"><span>Education / Credentials</span><textarea name="credentials" rows="4">{{ old('credentials',$profile->credentials ?? '') }}</textarea></label>
<label class="cm-field"><span>Photo</span><input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
<label class="cm-field"><span>Photo Alt Text</span><input name="alt_text" value="{{ old('alt_text',$profile->alt_text ?? '') }}" maxlength="250"></label>
<input type="hidden" name="sort_order" value="{{ old('sort_order',$profile->sort_order ?? 0) }}">
<div class="cm-publish-box">
<label class="cm-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$profile->is_featured ?? false))><span><strong>Featured / Leadership</strong></span></label>
<label class="cm-check"><input type="checkbox" name="consent_confirmed" value="1" @checked(old('consent_confirmed',isset($profile) && filled($profile->consent_confirmed_at)))><span><strong>Photo authorization confirmed</strong><small>Required before publishing an identifiable photo.</small></span></label>
<label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$profile->is_published ?? false))><span><strong>Publish profile</strong></span></label>
</div>
<div class="cm-compose-actions"><a class="cm-button cm-button--secondary" href="{{ route('admin.faculty.index') }}">Cancel</a><button class="cm-button cm-button--primary">Save Profile</button></div>
</form>
@endsection
