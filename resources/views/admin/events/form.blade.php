@php($editing = isset($event))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Event' : 'Add School Event'])

@section('content')
<section class="cm-page-head">
    <div><a class="cm-back-link" href="{{ route('admin.events.index') }}">&larr; Events</a><span class="cm-eyebrow">School calendar</span><h1>{{ $editing ? 'Edit Event' : 'Add School Event' }}</h1><p>Add the same information you would place on a school event announcement.</p></div>
</section>

<form method="POST" action="{{ $editing ? route('admin.events.update', $event) : route('admin.events.store') }}" class="cm-compose" data-cm-form>
    @csrf
    @if($editing) @method('PUT') @endif

    <label class="cm-field cm-field--large"><span>Event name</span><input name="title" value="{{ old('title', $event->title ?? '') }}" maxlength="180" required placeholder="Example: Parent Orientation"></label>
    <label class="cm-field"><span>What should families know?</span><textarea name="description" rows="9" maxlength="30000" required>{{ old('description', $event->description ?? '') }}</textarea></label>
    <label class="cm-field"><span>Venue / location</span><input name="venue" value="{{ old('venue', $event->venue ?? '') }}" maxlength="180" placeholder="Example: School Activity Hall"></label>

    <div class="cm-two">
        <label class="cm-field"><span>Starts</span><input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($event) ? $event->starts_at->format('Y-m-d\TH:i') : '') }}" required></label>
        <label class="cm-field"><span>Ends</span><input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($event) ? $event->ends_at->format('Y-m-d\TH:i') : '') }}" required></label>
    </div>

    <div class="cm-publish-box">
        <label class="cm-check"><input type="checkbox" name="is_all_day" value="1" @checked(old('is_all_day', $event->is_all_day ?? false))><span><strong>All-day event</strong><small>Use this when a precise time is not needed.</small></span></label>
        <label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', isset($event) && filled($event->published_at)))><span><strong>Publish now</strong><small>Turn off to save as a draft.</small></span></label>
    </div>

    <details class="cm-advanced">
        <summary>Optional registration link</summary>
        <label class="cm-field"><span>Registration webpage</span><input type="url" name="registration_url" value="{{ old('registration_url', $event->registration_url ?? '') }}" maxlength="500" placeholder="https://..."></label>
    </details>

    <div class="cm-compose-actions">
        <a href="{{ route('admin.events.index') }}" class="cm-button cm-button--secondary">Cancel</a>
        <button class="cm-button cm-button--primary">{{ $editing ? 'Save Changes' : 'Save Event' }}</button>
    </div>
</form>
@endsection