@extends('admin.layouts.app', ['title' => 'Edit About Page'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit About Page</h1>
        <p>Update approved words without changing the futuristic responsive layout, colors, spacing, or code.</p>
    </div>
    <a href="{{ route('about') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview About Page &nearr;</a>
</section>

<form method="POST" action="{{ route('admin.about-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>About Hero</h2><p>The first introduction families see.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge', $content['hero_badge']) }}" maxlength="80" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading', $content['hero_heading']) }}" maxlength="140" required></label>
                <label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight', $content['hero_highlight']) }}" maxlength="140" required></label>
            </div>
            <label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" maxlength="700" required>{{ old('hero_lead', $content['hero_lead']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>2</span><div><h2>School Story</h2><p>Use only school-approved historical information.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section label</span><input name="story_kicker" value="{{ old('story_kicker', $content['story_kicker']) }}" maxlength="80" required></label>
            <label class="cm-field"><span>Heading</span><input name="story_heading" value="{{ old('story_heading', $content['story_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>School history / story</span><textarea name="story_body" rows="9" maxlength="5000" required>{{ old('story_body', $content['story_body']) }}</textarea></label>
            <label class="cm-field"><span>Approval note</span><textarea name="story_note" rows="3" maxlength="1000" required>{{ old('story_note', $content['story_note']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>3</span><div><h2>Mission & Vision</h2><p>Paste the exact approved wording.</p></div></div>
        <div class="cm-fields">
            <div class="cm-two">
                <div>
                    <label class="cm-field"><span>Mission title</span><input name="mission_title" value="{{ old('mission_title', $content['mission_title']) }}" maxlength="100" required></label>
                    <label class="cm-field"><span>Official mission</span><textarea name="mission_text" rows="7" maxlength="2500" required>{{ old('mission_text', $content['mission_text']) }}</textarea></label>
                </div>
                <div>
                    <label class="cm-field"><span>Vision title</span><input name="vision_title" value="{{ old('vision_title', $content['vision_title']) }}" maxlength="100" required></label>
                    <label class="cm-field"><span>Official vision</span><textarea name="vision_text" rows="7" maxlength="2500" required>{{ old('vision_text', $content['vision_text']) }}</textarea></label>
                </div>
            </div>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>4</span><div><h2>Biblical Foundation</h2><p>Verify Scripture and approved faith wording before publishing.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section label</span><input name="faith_kicker" value="{{ old('faith_kicker', $content['faith_kicker']) }}" maxlength="80" required></label>
            <label class="cm-field"><span>Heading</span><input name="faith_heading" value="{{ old('faith_heading', $content['faith_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Faith statement summary</span><textarea name="faith_text" rows="6" maxlength="3500" required>{{ old('faith_text', $content['faith_text']) }}</textarea></label>
            <div class="cm-two">
                <label class="cm-field"><span>Scripture text</span><textarea name="verse_text" rows="4" maxlength="500" required>{{ old('verse_text', $content['verse_text']) }}</textarea></label>
                <label class="cm-field"><span>Reference / translation</span><input name="verse_reference" value="{{ old('verse_reference', $content['verse_reference']) }}" maxlength="100" required></label>
            </div>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>5</span><div><h2>Core Values</h2><p>Four cards stay visually protected.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section heading</span><input name="values_heading" value="{{ old('values_heading', $content['values_heading']) }}" maxlength="180" required></label>
            @foreach([1,2,3,4] as $number)
                <div class="cm-card-fields">
                    <strong>Value {{ $number }}</strong>
                    <label class="cm-field"><span>Title</span><input name="value_{{ $number }}_title" value="{{ old('value_'.$number.'_title', $content['value_'.$number.'_title']) }}" maxlength="80" required></label>
                    <label class="cm-field"><span>Description</span><textarea name="value_{{ $number }}_text" rows="3" maxlength="1000" required>{{ old('value_'.$number.'_text', $content['value_'.$number.'_text']) }}</textarea></label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>6</span><div><h2>Leadership Message</h2><p>Principal or school administrator welcome message.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section label</span><input name="leadership_kicker" value="{{ old('leadership_kicker', $content['leadership_kicker']) }}" maxlength="80" required></label>
            <label class="cm-field"><span>Heading</span><input name="leadership_heading" value="{{ old('leadership_heading', $content['leadership_heading']) }}" maxlength="180" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Leader name (optional)</span><input name="leader_name" value="{{ old('leader_name', $content['leader_name']) }}" maxlength="120"></label>
                <label class="cm-field"><span>Role / title</span><input name="leader_role" value="{{ old('leader_role', $content['leader_role']) }}" maxlength="120" required></label>
            </div>
            <label class="cm-field"><span>Welcome message</span><textarea name="leader_message" rows="9" maxlength="5000" required>{{ old('leader_message', $content['leader_message']) }}</textarea></label>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>7</span><div><h2>Community & Final Links</h2><p>Short summary and calls to action.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Community heading</span><input name="community_heading" value="{{ old('community_heading', $content['community_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Community text</span><textarea name="community_text" rows="5" maxlength="2000" required>{{ old('community_text', $content['community_text']) }}</textarea></label>
            <label class="cm-field"><span>Final heading</span><input name="cta_heading" value="{{ old('cta_heading', $content['cta_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Final message</span><textarea name="cta_text" rows="4" maxlength="1500" required>{{ old('cta_text', $content['cta_text']) }}</textarea></label>
            <div class="cm-two">
                <label class="cm-field"><span>Programs button</span><input name="cta_programs_button" value="{{ old('cta_programs_button', $content['cta_programs_button']) }}" maxlength="40" required><small>Destination remains locked to Programs.</small></label>
                <label class="cm-field"><span>Contact button</span><input name="cta_contact_button" value="{{ old('cta_contact_button', $content['cta_contact_button']) }}" maxlength="40" required><small>Destination remains locked to Contact.</small></label>
            </div>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>The responsive design remains protected at every screen size.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save About Page</button>
    </div>
</form>
@endsection