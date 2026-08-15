@extends('admin.layouts.app', ['title' => 'Edit Admissions Page'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit Admissions Page</h1>
        <p>Update public admissions information without changing the protected responsive layout or any existing admissions workflow.</p>
    </div>
    <a href="{{ route('admissions') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Admissions Page &nearr;</a>
</section>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.admissions-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>Admissions Hero</h2><p>Welcome families and explain what this page provides.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge', $content['hero_badge']) }}" maxlength="80" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading', $content['hero_heading']) }}" maxlength="160" required></label>
                <label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight', $content['hero_highlight']) }}" maxlength="160" required></label>
            </div>
            <label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" maxlength="1000" required>{{ old('hero_lead', $content['hero_lead']) }}</textarea></label>
            @if(!empty($content['hero_image_path']))
                <img src="{{ Storage::disk('public')->url($content['hero_image_path']) }}" alt="Current Admissions hero" class="cm-photo-preview">
            @endif
            <label class="cm-field"><span>Admissions hero image (optional)</span><input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp"></label>
            <label class="cm-check"><input type="checkbox" name="hero_image_authorized" value="1"><span><strong>I confirm this new admissions photograph is approved for website publication.</strong></span></label>
            <label class="cm-field"><span>Welcome heading</span><input name="welcome_heading" value="{{ old('welcome_heading', $content['welcome_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Welcome text</span><textarea name="welcome_text" rows="4" maxlength="1800" required>{{ old('welcome_text', $content['welcome_text']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>2</span><div><h2>Admissions Steps</h2><p>Four clear steps; edit only the approved wording.</p></div></div>
        <div class="cm-fields">
            @foreach([1,2,3,4] as $number)
                <div class="cm-card-fields">
                    <strong>Step {{ $number }}</strong>
                    <label class="cm-field"><span>Title</span><input name="step_{{ $number }}_title" value="{{ old('step_'.$number.'_title', $content['step_'.$number.'_title']) }}" maxlength="100" required></label>
                    <label class="cm-field"><span>Description</span><textarea name="step_{{ $number }}_text" rows="3" maxlength="1200" required>{{ old('step_'.$number.'_text', $content['step_'.$number.'_text']) }}</textarea></label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>3</span><div><h2>Requirements</h2><p>Replace placeholders with the exact current school-approved requirements.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Heading</span><input name="requirements_heading" value="{{ old('requirements_heading', $content['requirements_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Introduction</span><textarea name="requirements_intro" rows="4" maxlength="1800" required>{{ old('requirements_intro', $content['requirements_intro']) }}</textarea></label>
            @foreach([1,2,3,4] as $number)
                <div class="cm-card-fields">
                    <strong>Requirement {{ $number }}</strong>
                    <label class="cm-field"><span>Title</span><input name="requirement_{{ $number }}_title" value="{{ old('requirement_'.$number.'_title', $content['requirement_'.$number.'_title']) }}" maxlength="120" required></label>
                    <label class="cm-field"><span>Description</span><textarea name="requirement_{{ $number }}_text" rows="3" maxlength="1500" required>{{ old('requirement_'.$number.'_text', $content['requirement_'.$number.'_text']) }}</textarea></label>
                </div>
            @endforeach
            <label class="cm-field"><span>Requirements note</span><textarea name="requirements_note" rows="4" maxlength="1800" required>{{ old('requirements_note', $content['requirements_note']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>4</span><div><h2>Dates & Status</h2><p>Keep current dates and availability accurate.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Heading</span><input name="dates_heading" value="{{ old('dates_heading', $content['dates_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Dates / schedule guidance</span><textarea name="dates_text" rows="5" maxlength="2500" required>{{ old('dates_text', $content['dates_text']) }}</textarea></label>
            <div class="cm-two">
                <div>
                    <label class="cm-field"><span>School year label</span><input name="school_year_label" value="{{ old('school_year_label', $content['school_year_label']) }}" maxlength="80" required></label>
                    <label class="cm-field"><span>School year value</span><input name="school_year_value" value="{{ old('school_year_value', $content['school_year_value']) }}" maxlength="160" required></label>
                </div>
                <div>
                    <label class="cm-field"><span>Status label</span><input name="status_label" value="{{ old('status_label', $content['status_label']) }}" maxlength="80" required></label>
                    <label class="cm-field"><span>Status value</span><input name="status_value" value="{{ old('status_value', $content['status_value']) }}" maxlength="220" required></label>
                </div>
            </div>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>5</span><div><h2>Frequently Asked Questions</h2><p>Four expandable public FAQ items.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>FAQ heading</span><input name="faq_heading" value="{{ old('faq_heading', $content['faq_heading']) }}" maxlength="180" required></label>
            @foreach([1,2,3,4] as $number)
                <div class="cm-card-fields">
                    <strong>FAQ {{ $number }}</strong>
                    <label class="cm-field"><span>Question</span><input name="faq_{{ $number }}_q" value="{{ old('faq_'.$number.'_q', $content['faq_'.$number.'_q']) }}" maxlength="220" required></label>
                    <label class="cm-field"><span>Answer</span><textarea name="faq_{{ $number }}_a" rows="4" maxlength="1800" required>{{ old('faq_'.$number.'_a', $content['faq_'.$number.'_a']) }}</textarea></label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>6</span><div><h2>Privacy & Final Call to Action</h2><p>Public privacy reminder plus the final family action.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Privacy heading</span><input name="privacy_heading" value="{{ old('privacy_heading', $content['privacy_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Privacy message</span><textarea name="privacy_text" rows="5" maxlength="2000" required>{{ old('privacy_text', $content['privacy_text']) }}</textarea></label>
            <label class="cm-field"><span>Final heading</span><input name="cta_heading" value="{{ old('cta_heading', $content['cta_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Final message</span><textarea name="cta_text" rows="4" maxlength="1500" required>{{ old('cta_text', $content['cta_text']) }}</textarea></label>
            <div class="cm-two">
                <label class="cm-field"><span>Primary button text</span><input name="cta_primary_button" value="{{ old('cta_primary_button', $content['cta_primary_button']) }}" maxlength="40" required><small>Destination stays locked to Contact.</small></label>
                <label class="cm-field"><span>Secondary button text</span><input name="cta_secondary_button" value="{{ old('cta_secondary_button', $content['cta_secondary_button']) }}" maxlength="40" required><small>Destination stays locked to Programs.</small></label>
            </div>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>Public page design and admissions backend remain protected.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save Admissions Page</button>
    </div>
</form>
@endsection