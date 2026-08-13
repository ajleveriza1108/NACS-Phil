@php($editing = isset($galleryItem))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Photo' : 'Upload Photo'])

@section('content')
<section class="cm-page-head">
    <div><a class="cm-back-link" href="{{ route('admin.gallery.index') }}">&larr; Photos</a><span class="cm-eyebrow">School gallery</span><h1>{{ $editing ? 'Edit Photo' : 'Upload Photo' }}</h1><p>Choose an approved photograph, give it a clear title, then publish when permission is confirmed.</p></div>
</section>

<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.gallery.update', $galleryItem) : route('admin.gallery.store') }}" class="cm-compose" data-cm-form>
    @csrf
    @if($editing) @method('PUT') @endif

    @if($editing)
        <img src="{{ Storage::url($galleryItem->image_path) }}" alt="{{ $galleryItem->alt_text }}" class="cm-photo-preview">
    @endif

    <div class="cm-upload-box">
        <strong>{{ $editing ? 'Replace photograph (optional)' : 'Choose photograph' }}</strong>
        <p>JPEG, PNG, or WebP. Maximum 5 MB.</p>
        <label class="cm-file-button"><span>{{ $editing ? 'Choose Replacement' : 'Choose Photo' }}</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" {{ $editing ? '' : 'required' }} data-cm-file></label>
        <span class="cm-file-name" data-cm-file-name>No new photo selected</span>
    </div>

    <div class="cm-two">
        <label class="cm-field"><span>Photo title</span><input name="title" value="{{ old('title', $galleryItem->title ?? '') }}" maxlength="180" required placeholder="Example: Science Activity"></label>
        <label class="cm-field"><span>Album / category</span><input name="category" value="{{ old('category', $galleryItem->category ?? '') }}" maxlength="80" required placeholder="Campus, Preschool, Graduation..."></label>
    </div>

    <label class="cm-field"><span>Describe the photo for visitors who cannot see it</span><input name="alt_text" value="{{ old('alt_text', $galleryItem->alt_text ?? '') }}" maxlength="250" required placeholder="Example: Grade 4 learners working together on a classroom science activity."></label>
    <label class="cm-field"><span>Caption (optional)</span><textarea name="caption" rows="4" maxlength="3000">{{ old('caption', $galleryItem->caption ?? '') }}</textarea></label>

    <details class="cm-advanced">
        <summary>Optional photo details</summary>
        <div class="cm-two">
            <label class="cm-field"><span>Date taken</span><input type="date" name="taken_at" value="{{ old('taken_at', isset($galleryItem) && $galleryItem->taken_at ? $galleryItem->taken_at->format('Y-m-d') : '') }}"></label>
            <label class="cm-field"><span>Photographer credit</span><input name="photographer_credit" value="{{ old('photographer_credit', $galleryItem->photographer_credit ?? '') }}" maxlength="180"></label>
        </div>
    </details>

    <input type="hidden" name="sort_order" value="{{ old('sort_order', $galleryItem->sort_order ?? 0) }}">

    <div class="cm-consent-box">
        <h2>Before publishing</h2>
        <label class="cm-check"><input type="checkbox" name="consent_confirmed" value="1" @checked(old('consent_confirmed', isset($galleryItem) && filled($galleryItem->consent_confirmed_at)))><span><strong>School authorization and appropriate consent are confirmed</strong><small>This is especially important for identifiable children.</small></span></label>
        <label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $galleryItem->is_published ?? false))><span><strong>Publish on the public gallery</strong><small>Publishing remains blocked unless authorization is confirmed.</small></span></label>
    </div>

    <div class="cm-compose-actions">
        <a href="{{ route('admin.gallery.index') }}" class="cm-button cm-button--secondary">Cancel</a>
        <button class="cm-button cm-button--primary">{{ $editing ? 'Save Changes' : 'Save Photo' }}</button>
    </div>
</form>
@endsection