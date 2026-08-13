@php($editing = isset($document))
@extends('admin.layouts.app', ['title' => $editing ? 'Edit Document' : 'Add Document'])
@section('content')
<section class="cm-page-head"><div><a class="cm-back-link" href="{{ route('admin.documents.index') }}">&larr; Documents</a><h1>{{ $editing ? 'Edit Document' : 'Add Document' }}</h1></div></section>
<form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.documents.update',$document) : route('admin.documents.store') }}" class="cm-compose">
@csrf @if($editing) @method('PUT') @endif
<label class="cm-field"><span>Title</span><input name="title" value="{{ old('title',$document->title ?? '') }}" required maxlength="180"></label>
<label class="cm-field"><span>Description</span><textarea name="description" rows="4">{{ old('description',$document->description ?? '') }}</textarea></label>
<div class="cm-two"><label class="cm-field"><span>Category</span><input name="category" value="{{ old('category',$document->category ?? '') }}" required maxlength="100" placeholder="Handbook, Admissions, Forms..."></label><label class="cm-field"><span>School Year</span><input name="school_year" value="{{ old('school_year',$document->school_year ?? '') }}" maxlength="30"></label></div>
<div class="cm-two"><label class="cm-field"><span>Audience</span><select name="audience">@foreach(['public'=>'Public','parents'=>'Parents','applicants'=>'Applicants','staff'=>'Staff'] as $v=>$l)<option value="{{ $v }}" @selected(old('audience',$document->audience ?? 'public')===$v)>{{ $l }}</option>@endforeach</select></label><label class="cm-field"><span>Expiry Date</span><input type="datetime-local" name="expires_at" value="{{ old('expires_at',isset($document) && $document->expires_at ? $document->expires_at->format('Y-m-d\TH:i') : '') }}"></label></div>
<label class="cm-field"><span>{{ $editing ? 'Replace File (optional)' : 'File' }}</span><input type="file" name="file" {{ $editing ? '' : 'required' }}></label>
<input type="hidden" name="sort_order" value="{{ old('sort_order',$document->sort_order ?? 0) }}">
<label class="cm-check"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',isset($document) && filled($document->published_at)))><span><strong>Publish / make available to the selected audience</strong></span></label>
<div class="cm-compose-actions"><a class="cm-button cm-button--secondary" href="{{ route('admin.documents.index') }}">Cancel</a><button class="cm-button cm-button--primary">Save Document</button></div>
</form>
@endsection
