@extends('admin.layouts.app', ['title' => 'Edit Programs Page'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; Content Manager</a>
        <span class="cm-eyebrow">Website content</span>
        <h1>Edit Programs Page</h1>
        <p>Update program wording while the responsive layout, visual hierarchy, links, and code remain protected.</p>
    </div>
    <a href="{{ route('programs') }}" target="_blank" rel="noopener" class="cm-button cm-button--secondary">Preview Programs Page &nearr;</a>
</section>

<form method="POST" action="{{ route('admin.programs-content.update') }}" class="cm-editor" data-cm-form>
    @csrf
    @method('PATCH')

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>1</span><div><h2>Programs Hero</h2><p>Top-of-page introduction.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Small label</span><input name="hero_badge" value="{{ old('hero_badge', $content['hero_badge']) }}" maxlength="80" required></label>
            <div class="cm-two">
                <label class="cm-field"><span>Main heading</span><input name="hero_heading" value="{{ old('hero_heading', $content['hero_heading']) }}" maxlength="160" required></label>
                <label class="cm-field"><span>Highlighted words</span><input name="hero_highlight" value="{{ old('hero_highlight', $content['hero_highlight']) }}" maxlength="160" required></label>
            </div>
            <label class="cm-field"><span>Introduction</span><textarea name="hero_lead" rows="4" maxlength="900" required>{{ old('hero_lead', $content['hero_lead']) }}</textarea></label>
            <label class="cm-field"><span>Overview heading</span><input name="overview_heading" value="{{ old('overview_heading', $content['overview_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Overview text</span><textarea name="overview_text" rows="4" maxlength="1500" required>{{ old('overview_text', $content['overview_text']) }}</textarea></label>
        </div>
    </section>

    @foreach([
        'preschool' => ['2', 'Preschool / Early Years'],
        'elementary' => ['3', 'Elementary'],
        'junior' => ['4', 'Junior High'],
    ] as $prefix => $meta)
        <section class="cm-editor-section">
            <div class="cm-editor-section__title"><span>{{ $meta[0] }}</span><div><h2>{{ $meta[1] }}</h2><p>Edit the program summary and four feature bullets.</p></div></div>
            <div class="cm-fields">
                <div class="cm-two">
                    <label class="cm-field"><span>Section label</span><input name="{{ $prefix }}_kicker" value="{{ old($prefix.'_kicker', $content[$prefix.'_kicker']) }}" maxlength="80" required></label>
                    <label class="cm-field"><span>Program title</span><input name="{{ $prefix }}_title" value="{{ old($prefix.'_title', $content[$prefix.'_title']) }}" maxlength="100" required></label>
                </div>
                <label class="cm-field"><span>Levels / grades</span><input name="{{ $prefix }}_levels" value="{{ old($prefix.'_levels', $content[$prefix.'_levels']) }}" maxlength="180" required></label>
                <label class="cm-field"><span>Program description</span><textarea name="{{ $prefix }}_text" rows="6" maxlength="2500" required>{{ old($prefix.'_text', $content[$prefix.'_text']) }}</textarea></label>
                @foreach([1,2,3,4] as $number)
                    <label class="cm-field"><span>Feature {{ $number }}</span><input name="{{ $prefix }}_feature_{{ $number }}" value="{{ old($prefix.'_feature_'.$number, $content[$prefix.'_feature_'.$number]) }}" maxlength="160" required></label>
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>5</span><div><h2>Learning Approach</h2><p>Three protected cards: Know, Grow, Serve.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Section label</span><input name="approach_kicker" value="{{ old('approach_kicker', $content['approach_kicker']) }}" maxlength="80" required></label>
            <label class="cm-field"><span>Heading</span><input name="approach_heading" value="{{ old('approach_heading', $content['approach_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Introduction</span><textarea name="approach_text" rows="4" maxlength="1800" required>{{ old('approach_text', $content['approach_text']) }}</textarea></label>
            @foreach([1,2,3] as $number)
                <div class="cm-card-fields">
                    <strong>Card {{ $number }}</strong>
                    <label class="cm-field"><span>Title</span><input name="approach_{{ $number }}_title" value="{{ old('approach_'.$number.'_title', $content['approach_'.$number.'_title']) }}" maxlength="80" required></label>
                    <label class="cm-field"><span>Description</span><textarea name="approach_{{ $number }}_text" rows="3" maxlength="1000" required>{{ old('approach_'.$number.'_text', $content['approach_'.$number.'_text']) }}</textarea></label>
                </div>
            @endforeach
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>6</span><div><h2>Faith Integration</h2><p>Use school-approved wording and verify Scripture before publishing.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Heading</span><input name="faith_heading" value="{{ old('faith_heading', $content['faith_heading']) }}" maxlength="200" required></label>
            <label class="cm-field"><span>Explanation</span><textarea name="faith_text" rows="6" maxlength="2500" required>{{ old('faith_text', $content['faith_text']) }}</textarea></label>
            <div class="cm-two">
                <label class="cm-field"><span>Scripture text</span><textarea name="verse_text" rows="4" maxlength="500" required>{{ old('verse_text', $content['verse_text']) }}</textarea></label>
                <label class="cm-field"><span>Reference / translation</span><input name="verse_reference" value="{{ old('verse_reference', $content['verse_reference']) }}" maxlength="100" required></label>
            </div>
        </div>
    </section>

    <section class="cm-editor-section">
        <div class="cm-editor-section__title"><span>7</span><div><h2>Final Call to Action</h2><p>Bottom-of-page Admissions and Contact links.</p></div></div>
        <div class="cm-fields">
            <label class="cm-field"><span>Heading</span><input name="cta_heading" value="{{ old('cta_heading', $content['cta_heading']) }}" maxlength="180" required></label>
            <label class="cm-field"><span>Message</span><textarea name="cta_text" rows="4" maxlength="1500" required>{{ old('cta_text', $content['cta_text']) }}</textarea></label>
            <div class="cm-two">
                <label class="cm-field"><span>Admissions button</span><input name="cta_admissions_button" value="{{ old('cta_admissions_button', $content['cta_admissions_button']) }}" maxlength="40" required><small>Destination stays locked to Admissions.</small></label>
                <label class="cm-field"><span>Contact button</span><input name="cta_contact_button" value="{{ old('cta_contact_button', $content['cta_contact_button']) }}" maxlength="40" required><small>Destination stays locked to Contact.</small></label>
            </div>
        </div>
    </section>

    <div class="cm-save-bar">
        <div><strong>Ready to save?</strong><small>Page structure and responsive behavior remain protected.</small></div>
        <button type="submit" class="cm-button cm-button--primary">Save Programs Page</button>
    </div>
</form>
@endsection