@extends('admin.layouts.app', ['title' => 'Edit Contact Page'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit Contact Page</h1>
        <p>Edit public contact details and page wording. Submitted family inquiries remain in the existing Inquiries area.</p>
    </div>
    <a href="{{ route('contact') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Contact Page &nearr;</a>
</section>

<div class="cm-editor-section">
    <div class="cm-editor-section__title">
        <span>I</span>
        <div>
            <h2>Review submitted inquiries</h2>
            <p>Family messages, status tracking, and private staff notes remain in the existing Inquiries workflow.</p>
        </div>
    </div>
    <div class="cm-fields">
        <a class="cm-button cm-button--primary" href="{{ route('admin.inquiries.index') }}">Open Inquiries</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.contact-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>Contact Hero</h2><p>Introduction at the top of the public Contact page.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge',$content['hero_badge']) }}" maxlength="100" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading',$content['hero_heading']) }}" maxlength="160" required></label>
                <label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight',$content['hero_highlight']) }}" maxlength="160" required></label>
            </div>
            <label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" maxlength="1200" required>{{ old('hero_lead',$content['hero_lead']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>2</span><div><h2>School Contact Details</h2><p>Only enter details that the school has officially verified.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section heading</span><input name="office_heading" value="{{ old('office_heading',$content['office_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Section text</span><textarea name="office_text" rows="3" maxlength="1800" required>{{ old('office_text',$content['office_text']) }}</textarea></label>
            <label class="cm-field"><span>Address</span><textarea name="address" rows="3" maxlength="500" required>{{ old('address',$content['address']) }}</textarea></label>
            <div class="cm-two">
                <label class="cm-field"><span>Phone</span><input name="phone" value="{{ old('phone',$content['phone']) }}" maxlength="80" placeholder="Leave blank until verified"></label>
                <label class="cm-field"><span>Email</span><input name="email" type="email" value="{{ old('email',$content['email']) }}" maxlength="150" placeholder="Leave blank until verified"></label>
            </div>
            <label class="cm-field"><span>Facebook URL</span><input name="facebook_url" type="url" value="{{ old('facebook_url',$content['facebook_url']) }}" maxlength="500"></label>
            <label class="cm-field"><span>Office hours</span><input name="office_hours" value="{{ old('office_hours',$content['office_hours']) }}" maxlength="300" placeholder="Optional — enter only verified hours"></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>3</span><div><h2>Inquiry Form</h2><p>Wording around the existing secure inquiry workflow.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Form heading</span><input name="inquiry_heading" value="{{ old('inquiry_heading',$content['inquiry_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Form guidance</span><textarea name="inquiry_text" rows="4" maxlength="1800" required>{{ old('inquiry_text',$content['inquiry_text']) }}</textarea></label>
            <label class="cm-field"><span>Privacy heading</span><input name="privacy_heading" value="{{ old('privacy_heading',$content['privacy_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Privacy text</span><textarea name="privacy_text" rows="4" maxlength="1800" required>{{ old('privacy_text',$content['privacy_text']) }}</textarea></label>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>This changes Contact page settings only—not submitted inquiries.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save Contact Page</button>
    </div>
</form>
@endsection