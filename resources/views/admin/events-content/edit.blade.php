@extends('admin.layouts.app', ['title' => 'Edit Events Page'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit Events Page</h1>
        <p>Edit the public Events page introduction and labels. Actual event records remain in the existing Events manager.</p>
    </div>
    <a href="{{ route('events.index') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Events Page &nearr;</a>
</section>

<div class="cm-editor-section">
    <div class="cm-editor-section__title"><span>E</span><div><h2>Manage actual events</h2><p>Create dates, venues, descriptions, publishing status and registration links in the existing Events manager.</p></div></div>
    <div class="cm-fields"><a class="cm-button cm-button--primary" href="{{ route('admin.events.index') }}">Open Events Manager</a></div>
</div>

<form method="POST" action="{{ route('admin.events-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>Events Hero</h2><p>Public introduction at the top of the page.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge',$content['hero_badge']) }}" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading',$content['hero_heading']) }}" required></label>
                <label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight',$content['hero_highlight']) }}" required></label>
            </div>
            <label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" required>{{ old('hero_lead',$content['hero_lead']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>2</span><div><h2>Event Listing</h2><p>Heading and empty-state wording.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Listing heading</span><input name="listing_heading" value="{{ old('listing_heading',$content['listing_heading']) }}" required></label>
            <label class="cm-field"><span>Listing text</span><textarea name="listing_text" rows="4" required>{{ old('listing_text',$content['listing_text']) }}</textarea></label>
            <label class="cm-field"><span>Empty heading</span><input name="empty_heading" value="{{ old('empty_heading',$content['empty_heading']) }}" required></label>
            <label class="cm-field"><span>Empty text</span><textarea name="empty_text" rows="3" required>{{ old('empty_text',$content['empty_text']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>3</span><div><h2>Event Detail Page</h2><p>Labels around each event's actual stored content.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Back link label</span><input name="detail_back_label" value="{{ old('detail_back_label',$content['detail_back_label']) }}" required></label>
            <label class="cm-field"><span>Contact heading</span><input name="detail_contact_heading" value="{{ old('detail_contact_heading',$content['detail_contact_heading']) }}" required></label>
            <label class="cm-field"><span>Contact text</span><textarea name="detail_contact_text" rows="4" required>{{ old('detail_contact_text',$content['detail_contact_text']) }}</textarea></label>
            <label class="cm-field"><span>Contact button</span><input name="detail_contact_button" value="{{ old('detail_contact_button',$content['detail_contact_button']) }}" required></label>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>This changes Events page labels only—not event records.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save Events Page</button>
    </div>
</form>
@endsection