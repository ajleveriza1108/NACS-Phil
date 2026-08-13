@php($editing = isset($entry))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Calendar Date' : 'Add Calendar Date'])
@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.calendar.index') }}">&larr; Academic Calendar</a><h1>{{ $editing ? 'Edit Calendar Date' : 'Add Calendar Date' }}</h1></div></section>
<form method="POST" action="{{ $editing ? route('admin.calendar.update',$entry) : route('admin.calendar.store') }}" class="cm-compose">
@csrf @if($editing) @method('PUT') @endif
<label class="cm-field"><span>Title</span><input name="title" required maxlength="180" value="{{ old('title',$entry->title ?? '') }}"></label>
<div class="cm-two"><label class="cm-field"><span>Category</span><select name="category">@foreach($categories as $v=>$l)<option value="{{ $v }}" @selected(old('category',$entry->category ?? 'academic')===$v)>{{ $l }}</option>@endforeach</select></label><label class="cm-field"><span>School Year</span><input name="school_year" value="{{ old('school_year',$entry->school_year ?? '') }}" maxlength="30"></label></div>
<label class="cm-field"><span>Description</span><textarea name="description" rows="4">{{ old('description',$entry->description ?? '') }}</textarea></label>
<div class="cm-two"><label class="cm-field"><span>Starts</span><input type="datetime-local" name="starts_at" required value="{{ old('starts_at',isset($entry)?$entry->starts_at->format('Y-m-d\TH:i'):'') }}"></label><label class="cm-field"><span>Ends</span><input type="datetime-local" name="ends_at" required value="{{ old('ends_at',isset($entry)?$entry->ends_at->format('Y-m-d\TH:i'):'') }}"></label></div>
<div class="cm-publish-box"><label class="cm-check"><input type="checkbox" name="is_all_day" value="1" @checked(old('is_all_day',$entry->is_all_day ?? false))><span><strong>All day</strong></span></label><label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$entry->is_published ?? false))><span><strong>Publish</strong></span></label></div>
<div class="cm-compose-actions"><a class="cm-button cm-button--secondary" href="{{ route('admin.calendar.index') }}">Cancel</a><button class="cm-button cm-button--primary">Save Date</button></div>
</form>
@endsection
