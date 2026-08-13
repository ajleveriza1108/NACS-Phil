@php($editing = isset($announcement))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Announcement' : 'Post Announcement'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.announcements.index') }}">&larr; Announcements</a>
        <span class="cm-eyebrow">Post to the school website</span>
        <h1>{{ $editing ? 'Edit Announcement' : 'Post Announcement' }}</h1>
        <p>Write it much like a social-media post. Leave â€œPublish nowâ€ off when you only want to save a draft.</p>
    </div>
</section>

<form method="POST" action="{{ $editing ? route('admin.announcements.update', $announcement) : route('admin.announcements.store') }}" class="cm-compose" data-cm-form>
    @csrf
    @if($editing) @method('PUT') @endif

    <label class="cm-field cm-field--large">
        <span>Announcement title</span>
        <input name="title" value="{{ old('title', $announcement->title ?? '') }}" maxlength="180" required placeholder="Example: Enrollment for SY 2026â€“2027">
    </label>

    <label class="cm-field">
        <span>Short preview</span>
        <textarea name="excerpt" rows="3" maxlength="400" placeholder="One or two sentences parents can understand at a glance.">{{ old('excerpt', $announcement->excerpt ?? '') }}</textarea>
    </label>

    <label class="cm-field">
        <span>Complete announcement</span>
        <textarea name="body" rows="12" maxlength="30000" required placeholder="Write the complete announcement here...">{{ old('body', $announcement->body ?? '') }}</textarea>
    </label>

    <div class="cm-two">
        <label class="cm-field">
            <span>Category</span>
            <select name="type">
                @foreach(['info'=>'School Information','enrollment'=>'Enrollment','event'=>'School Event','urgent'=>'Urgent Notice'] as $value=>$label)
                    <option value="{{ $value }}" @selected(old('type', $announcement->type ?? 'info') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div class="cm-publish-box">
            <label class="cm-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $announcement->is_featured ?? false))><span><strong>Feature on Homepage</strong><small>Use only for an important current notice.</small></span></label>
            <label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', isset($announcement) && filled($announcement->published_at)))><span><strong>Publish now</strong><small>Turn off to keep this as a draft.</small></span></label>
        </div>
    </div>

    <details class="cm-advanced">
        <summary>Optional schedule</summary>
        <div class="cm-two">
            <label class="cm-field"><span>Show from</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($announcement) && $announcement->starts_at ? $announcement->starts_at->format('Y-m-d\TH:i') : '') }}"></label>
            <label class="cm-field"><span>Hide after</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($announcement) && $announcement->ends_at ? $announcement->ends_at->format('Y-m-d\TH:i') : '') }}"><small>Useful for â€œNo classes tomorrowâ€ and limited-time notices.</small></label>
        </div>
    </details>

    <input type="hidden" name="sort_order" value="{{ old('sort_order', $announcement->sort_order ?? 0) }}">

    <div class="cm-compose-actions">
        <a href="{{ route('admin.announcements.index') }}" class="cm-button cm-button--secondary">Cancel</a>
        <button class="cm-button cm-button--primary">{{ $editing ? 'Save Changes' : 'Save Announcement' }}</button>
    </div>
</form>
@endsection