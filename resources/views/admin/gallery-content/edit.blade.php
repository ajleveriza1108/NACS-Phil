@extends('admin.layouts.app', ['title'=>'Edit Gallery Page'])
@section('content')
<section class="cm-page-head">
<div><a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a><span class="cm-eyebrow">Website content</span><h1>Edit Gallery Page</h1><p>Edit public Gallery wording. Actual photographs remain in the Photos manager.</p></div>
<a href="{{ route('gallery.index') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Gallery &nearr;</a>
</section>
<div class="cm-editor-section"><div class="cm-editor-section__title"><span>P</span><div><h2>Manage photographs</h2><p>Upload photos, write alt text and captions, select categories, and confirm authorization/consent in the existing Photos manager.</p></div></div><div class="cm-fields"><a class="cm-button cm-button--primary" href="{{ route('admin.gallery.index') }}">Open Photos Manager</a></div></div>
<form method="POST" action="{{ route('admin.gallery-content.update') }}" class="cm-editor">@csrf @method('PATCH')
<section class="cm-editor-section"><div class="cm-editor-section__title"><span>1</span><div><h2>Gallery Hero</h2></div></div><div class="cm-fields">
<label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge',$content['hero_badge']) }}" required></label>
<div class="cm-two"><label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading',$content['hero_heading']) }}" required></label><label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight',$content['hero_highlight']) }}" required></label></div>
<label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" required>{{ old('hero_lead',$content['hero_lead']) }}</textarea></label>
</div></section>
<section class="cm-editor-section"><div class="cm-editor-section__title"><span>2</span><div><h2>Photo Collection</h2></div></div><div class="cm-fields">
<label class="cm-field"><span>Listing heading</span><input name="listing_heading" value="{{ old('listing_heading',$content['listing_heading']) }}" required></label>
<label class="cm-field"><span>Listing text</span><textarea name="listing_text" rows="4" required>{{ old('listing_text',$content['listing_text']) }}</textarea></label>
<label class="cm-field"><span>Empty heading</span><input name="empty_heading" value="{{ old('empty_heading',$content['empty_heading']) }}" required></label>
<label class="cm-field"><span>Empty text</span><textarea name="empty_text" rows="3" required>{{ old('empty_text',$content['empty_text']) }}</textarea></label>
</div></section>
<section class="cm-editor-section"><div class="cm-editor-section__title"><span>3</span><div><h2>Privacy Reminder</h2></div></div><div class="cm-fields">
<label class="cm-field"><span>Heading</span><input name="privacy_heading" value="{{ old('privacy_heading',$content['privacy_heading']) }}" required></label>
<label class="cm-field"><span>Message</span><textarea name="privacy_text" rows="4" required>{{ old('privacy_text',$content['privacy_text']) }}</textarea></label>
<label class="cm-field"><span>Privacy button</span><input name="privacy_button" value="{{ old('privacy_button',$content['privacy_button']) }}" required></label>
</div></section>
<div class="cm-save-bar"><div><strong>Ready to save?</strong><small>This changes Gallery page wording only.</small></div><button class="cm-button cm-button--primary">Save Gallery Page</button></div>
</form>
@endsection