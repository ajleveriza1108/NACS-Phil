@php($editing = isset($announcement))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Announcement' : 'Post Announcement'])

@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.announcements.index') }}">&larr; Announcements</a><span class="cm-eyebrow">Post to the school website</span><h1>{{ $editing ? 'Edit Announcement' : 'Post Announcement' }}</h1><p>Teachers can save drafts and submit them for Principal review. Principal and Super Admin can publish or schedule directly.</p></div></section>

<form method="POST" action="{{ $editing ? route('admin.announcements.update',$announcement) : route('admin.announcements.store') }}" class="cm-compose">
@csrf @if($editing) @method('PUT') @endif
<label class="cm-field cm-field--large"><span>Announcement Title</span><input name="title" value="{{ old('title',$announcement->title ?? '') }}" maxlength="180" required></label>
<label class="cm-field"><span>Short Preview</span><textarea name="excerpt" rows="3" maxlength="400">{{ old('excerpt',$announcement->excerpt ?? '') }}</textarea></label>
<label class="cm-field"><span>Complete Announcement</span><textarea name="body" rows="12" maxlength="30000" required>{{ old('body',$announcement->body ?? '') }}</textarea></label>

<div class="cm-two">
<label class="cm-field"><span>Category</span><select name="type">@foreach(['info'=>'General / School Information','enrollment'=>'Admissions / Enrollment','event'=>'Activity / Event','urgent'=>'Emergency / Important'] as $v=>$l)<option value="{{ $v }}" @selected(old('type',$announcement->type ?? 'info')===$v)>{{ $l }}</option>@endforeach</select></label>
<label class="cm-field"><span>Audience</span><select name="audience">@foreach(['public'=>'Public','parents'=>'Parents','applicants'=>'Applicants','staff'=>'Staff'] as $v=>$l)<option value="{{ $v }}" @selected(old('audience',$announcement->audience ?? 'public')===$v)>{{ $l }}</option>@endforeach</select></label>
</div>

<div class="cm-publish-box">
<label class="cm-check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$announcement->is_featured ?? false))><span><strong>Feature on Homepage</strong></span></label>
<label class="cm-check"><input type="checkbox" name="is_pinned" value="1" @checked(old('is_pinned',$announcement->is_pinned ?? false))><span><strong>Pin to Top</strong></span></label>
@if(!auth()->user()->isTeacher())<label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',isset($announcement) && $announcement->workflow_status==='published'))><span><strong>Publish / Schedule</strong></span></label>@endif
</div>

<details class="cm-advanced" open><summary>Schedule &amp; Expiry</summary><div class="cm-two">
<label class="cm-field"><span>Publish At</span><input type="datetime-local" name="publish_at" value="{{ old('publish_at',isset($announcement) && $announcement->scheduled_publish_at ? $announcement->scheduled_publish_at->format('Y-m-d\TH:i') : '') }}"><small>Leave blank for immediate publication after approval.</small></label>
<label class="cm-field"><span>Show From</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at',isset($announcement) && $announcement->starts_at ? $announcement->starts_at->format('Y-m-d\TH:i') : '') }}"></label>
<label class="cm-field"><span>Hide / Expire After</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at',isset($announcement) && $announcement->ends_at ? $announcement->ends_at->format('Y-m-d\TH:i') : '') }}"></label>
</div></details>
<input type="hidden" name="sort_order" value="{{ old('sort_order',$announcement->sort_order ?? 0) }}">

@if(isset($announcement) && $announcement->review_notes)<div class="cm-alert"><strong>Review note:</strong> {{ $announcement->review_notes }}</div>@endif
<div class="cm-compose-actions">
<a class="cm-button cm-button--secondary" href="{{ route('admin.announcements.index') }}">Cancel</a>
@if(auth()->user()->isTeacher())
<button name="action" value="save" class="cm-button cm-button--secondary">Save Draft</button>
<button name="action" value="submit_review" class="cm-button cm-button--primary">Submit for Review</button>
@else
<button name="action" value="save" class="cm-button cm-button--primary">{{ $editing ? 'Save Changes' : 'Save Announcement' }}</button>
@endif
</div>
</form>
@endsection
