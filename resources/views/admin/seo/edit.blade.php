@extends('admin.layouts.app', ['title' => 'SEO & Sharing'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">Search & social</span><h1>SEO &amp; Sharing</h1><p>Manage search descriptions, social sharing text, canonical URLs, and indexing preferences. Advanced application secrets are not exposed here.</p></div></section>
<form method="POST" action="{{ route('admin.seo.update') }}" class="cm-compose">@csrf @method('PATCH')
@foreach($pages as $key=>$label)
@php($item = $settings->get($key))
<details class="cm-advanced" {{ $loop->first ? 'open' : '' }}><summary>{{ $label }}</summary>
<div class="cm-two">
<label class="cm-field"><span>SEO Title</span><input name="pages[{{ $key }}][title]" value="{{ old("pages.$key.title",$item?->title) }}" maxlength="180"></label>
<label class="cm-field"><span>Canonical URL</span><input type="url" name="pages[{{ $key }}][canonical_url]" value="{{ old("pages.$key.canonical_url",$item?->canonical_url) }}" maxlength="500"></label>
</div>
<label class="cm-field"><span>Meta Description</span><textarea name="pages[{{ $key }}][meta_description]" rows="3" maxlength="320">{{ old("pages.$key.meta_description",$item?->meta_description) }}</textarea></label>
<div class="cm-two"><label class="cm-field"><span>Social Title</span><input name="pages[{{ $key }}][social_title]" value="{{ old("pages.$key.social_title",$item?->social_title) }}" maxlength="180"></label><label class="cm-field"><span>Social Description</span><textarea name="pages[{{ $key }}][social_description]" rows="3" maxlength="320">{{ old("pages.$key.social_description",$item?->social_description) }}</textarea></label></div>
<label class="cm-check"><input type="hidden" name="pages[{{ $key }}][no_index]" value="0"><input type="checkbox" name="pages[{{ $key }}][no_index]" value="1" @checked(old("pages.$key.no_index",$item?->no_index ?? false))><span><strong>Do not index this page</strong></span></label>
</details>
@endforeach
<button class="cm-button cm-button--primary">Save SEO Settings</button>
</form>
@endsection
