@extends('admin.layouts.app', ['title' => 'Edit Homepage'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit Homepage</h1>
        <p>Change the words and approved hero photograph. The futuristic Phase 1 layout, colors, spacing, and code are protected.</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Homepage &nearr;</a>
</section>

<form method="POST" enctype="multipart/form-data" action="{{ route('admin.website-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title">
            <span>1</span>
            <div><h2>Hero / First Screen</h2><p>The first message families see when they open the website.</p></div>
        </div>

        <div class="cm-fields">
            <label class="cm-field">
                <span>Small label</span>
                <input name="hero_badge" value="{{ old('hero_badge', $content['hero_badge']) }}" maxlength="80" required>
            </label>
            <div class="cm-two">
                <label class="cm-field">
                    <span>Main heading</span>
                    <input name="hero_heading" value="{{ old('hero_heading', $content['hero_heading']) }}" maxlength="120" required>
                </label>
                <label class="cm-field">
                    <span>Highlighted words</span>
                    <input name="hero_highlight" value="{{ old('hero_highlight', $content['hero_highlight']) }}" maxlength="120" required>
                </label>
            </div>
            <label class="cm-field">
                <span>Short introduction</span>
                <textarea name="hero_lead" rows="4" maxlength="600" required>{{ old('hero_lead', $content['hero_lead']) }}</textarea>
            </label>
            <div class="cm-two">
                <label class="cm-field">
                    <span>Programs button text</span>
                    <input name="hero_primary_button" value="{{ old('hero_primary_button', $content['hero_primary_button']) }}" maxlength="40" required>
                    <small>Destination stays safely linked to Programs.</small>
                </label>
                <label class="cm-field">
                    <span>Admissions button text</span>
                    <input name="hero_secondary_button" value="{{ old('hero_secondary_button', $content['hero_secondary_button']) }}" maxlength="40" required>
                    <small>Destination stays safely linked to Admissions.</small>
                </label>
            </div>

            <div class="cm-upload-box">
                <div>
                    <strong>Homepage hero photograph</strong>
                    <p>Optional. If you do not upload one, the current concept image remains.</p>
                    @if(!empty($content['hero_image_path']))
                        <img src="{{ Storage::url($content['hero_image_path']) }}" alt="{{ $content['hero_image_alt'] }}" class="cm-current-image">
                    @endif
                </div>
                <label class="cm-file-button">
                    <span>Choose Photo</span>
                    <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" data-cm-file>
                </label>
                <span class="cm-file-name" data-cm-file-name>No new photo selected</span>
                <label class="cm-field">
                    <span>Photo description for accessibility</span>
                    <input name="hero_image_alt" value="{{ old('hero_image_alt', $content['hero_image_alt']) }}" maxlength="250" required>
                </label>
                <label class="cm-check cm-check--warning">
                    <input type="checkbox" name="hero_image_authorized" value="1">
                    <span><strong>Approved for website publication</strong><small>Check this only when uploading a new photograph and the school has appropriate permission to use it.</small></span>
                </label>
            </div>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title">
            <span>2</span>
            <div><h2>Why Choose NACS-Phil</h2><p>Edit the message inside the four existing cards. Their design stays locked.</p></div>
        </div>

        <div class="cm-fields">
            <label class="cm-field"><span>Section heading</span><input name="why_heading" value="{{ old('why_heading', $content['why_heading']) }}" maxlength="160" required></label>
            <label class="cm-field"><span>Section introduction</span><textarea name="why_intro" rows="3" maxlength="600" required>{{ old('why_intro', $content['why_intro']) }}</textarea></label>

            @foreach([1,2,3,4] as $number)
                <div class="cm-card-fields">
                    <strong>Card {{ $number }}</strong>
                    <label class="cm-field"><span>Title</span><input name="why_{{ $number }}_title" value="{{ old('why_'.$number.'_title', $content['why_'.$number.'_title']) }}" maxlength="80" required></label>
                    <label class="cm-field"><span>Short description</span><textarea name="why_{{ $number }}_text" rows="3" maxlength="500" required>{{ old('why_'.$number.'_text', $content['why_'.$number.'_text']) }}</textarea></label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title">
            <span>3</span>
            <div><h2>Programs Preview</h2><p>Only the homepage summaries. Full Programs-page editing will come in its own page phase.</p></div>
        </div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section heading</span><input name="programs_heading" value="{{ old('programs_heading', $content['programs_heading']) }}" maxlength="160" required></label>
            <label class="cm-field"><span>Preschool summary</span><textarea name="preschool_text" rows="3" maxlength="500" required>{{ old('preschool_text', $content['preschool_text']) }}</textarea></label>
            <label class="cm-field"><span>Elementary summary</span><textarea name="elementary_text" rows="3" maxlength="500" required>{{ old('elementary_text', $content['elementary_text']) }}</textarea></label>
            <label class="cm-field"><span>Junior High summary</span><textarea name="junior_high_text" rows="3" maxlength="500" required>{{ old('junior_high_text', $content['junior_high_text']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title">
            <span>4</span>
            <div><h2>Updates, Student Life & Admissions</h2><p>Keep these short and easy for parents to scan.</p></div>
        </div>
        <div class="cm-fields">
            <label class="cm-field"><span>School updates heading</span><input name="updates_heading" value="{{ old('updates_heading', $content['updates_heading']) }}" maxlength="160" required></label>
            <label class="cm-field"><span>School updates introduction</span><textarea name="updates_intro" rows="3" maxlength="600" required>{{ old('updates_intro', $content['updates_intro']) }}</textarea></label>
            <label class="cm-field"><span>Student life heading</span><input name="life_heading" value="{{ old('life_heading', $content['life_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Admissions call-to-action heading</span><input name="cta_heading" value="{{ old('cta_heading', $content['cta_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Admissions short message</span><textarea name="cta_text" rows="3" maxlength="600" required>{{ old('cta_text', $content['cta_text']) }}</textarea></label>
            <label class="cm-field"><span>Admissions button text</span><input name="cta_button" value="{{ old('cta_button', $content['cta_button']) }}" maxlength="40" required></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title">
            <span>5</span>
            <div><h2>Homepage Footer Contact</h2><p>These fields affect the Phase 1 homepage footer only. Global footer design will be standardized later.</p></div>
        </div>
        <div class="cm-fields">
            <label class="cm-field"><span>Short school tagline</span><input name="footer_tagline" value="{{ old('footer_tagline', $content['footer_tagline']) }}" maxlength="100" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Phone</span><input name="contact_phone" value="{{ old('contact_phone', $content['contact_phone']) }}" maxlength="80"></label>
                <label class="cm-field"><span>Email</span><input type="email" name="contact_email" value="{{ old('contact_email', $content['contact_email']) }}" maxlength="150"></label>
            </div>
            <label class="cm-field"><span>School address</span><textarea name="contact_address" rows="3" maxlength="300">{{ old('contact_address', $content['contact_address']) }}</textarea></label>
            <label class="cm-field"><span>Official Facebook page link</span><input type="url" name="facebook_url" value="{{ old('facebook_url', $content['facebook_url']) }}" maxlength="500" placeholder="https://www.facebook.com/..."></label>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>After saving, use Preview Homepage to check the public result.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save Homepage Changes</button>
    </div>
</form>
@endsection