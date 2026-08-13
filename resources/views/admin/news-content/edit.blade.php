@extends('admin.layouts.app', ['title' => 'Edit News Page'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit News Page</h1>
        <p>Edit the public News page introduction and labels. To create or edit actual posts, continue using Announcements.</p>
    </div>
    <a href="{{ route('announcements.index') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview News Page &nearr;</a>
</section>

<div class="cm-editor-section">
    <div class="cm-editor-section__title">
        <span>N</span>
        <div>
            <h2>Posting announcements</h2>
            <p>Actual news posts remain in the existing teacher-friendly Announcements workflow.</p>
        </div>
    </div>
    <div class="cm-fields">
        <a class="cm-button cm-button--primary" href="{{ route('admin.announcements.index') }}">Open Announcements</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.news-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>News Hero</h2><p>Public introduction above the announcement feed.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge', $content['hero_badge']) }}" maxlength="100" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading', $content['hero_heading']) }}" maxlength="160" required></label>
                <label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight', $content['hero_highlight']) }}" maxlength="160" required></label>
            </div>
            <label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" maxlength="1000" required>{{ old('hero_lead', $content['hero_lead']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>2</span><div><h2>Announcement Feed</h2><p>Heading and empty-state wording.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Listing heading</span><input name="listing_heading" value="{{ old('listing_heading', $content['listing_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Listing description</span><textarea name="listing_text" rows="4" maxlength="1800" required>{{ old('listing_text', $content['listing_text']) }}</textarea></label>
            <label class="cm-field"><span>Empty-state heading</span><input name="empty_heading" value="{{ old('empty_heading', $content['empty_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Empty-state description</span><textarea name="empty_text" rows="3" maxlength="1200" required>{{ old('empty_text', $content['empty_text']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>3</span><div><h2>Announcement Detail Page</h2><p>Labels around the actual announcement content.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Back link</span><input name="detail_back_label" value="{{ old('detail_back_label', $content['detail_back_label']) }}" maxlength="60" required></label>
            <label class="cm-field"><span>Footer heading</span><input name="detail_footer_heading" value="{{ old('detail_footer_heading', $content['detail_footer_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Footer message</span><textarea name="detail_footer_text" rows="4" maxlength="1500" required>{{ old('detail_footer_text', $content['detail_footer_text']) }}</textarea></label>
            <label class="cm-field"><span>Contact button text</span><input name="detail_contact_button" value="{{ old('detail_contact_button', $content['detail_contact_button']) }}" maxlength="40" required><small>Destination remains locked to Contact.</small></label>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>This changes News page labels onlyâ€”not announcement records.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save News Page</button>
    </div>
</form>
@endsection