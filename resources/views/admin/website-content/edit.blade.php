@extends('admin.layouts.app', ['title' => 'Visual Homepage Editor'])

@section('content')
@php
    $groups = collect($schema)->groupBy('group', true);
@endphp

<section class="ve-shell" data-ve-editor>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.website-content.update') }}" class="ve-panel">
        @csrf
        @method('PATCH')

        <div class="ve-panel__head">
            <span class="ve-lock">LOCKED DESIGN</span>
            <h1>Visual Homepage Editor</h1>
            <p>Click editable wording in the live page or use the fields below. Editors can change approved text and images, but not the layout, colors, spacing, code, or navigation structure.</p>
            <div class="ve-section-nav">
                <a href="{{ route('admin.about-content.edit') }}">About</a>
                <a href="{{ route('admin.programs-content.edit') }}">Programs</a>
                <a href="{{ route('admin.admissions-content.edit') }}">Admissions</a>
                <a href="{{ route('admin.news-content.edit') }}">News</a>
                <a href="{{ route('admin.events-content.edit') }}">Events</a>
                <a href="{{ route('admin.gallery-content.edit') }}">Gallery</a>
                <a href="{{ route('admin.contact-content.edit') }}">Contact</a>
            </div>
        </div>

        @foreach($groups as $groupName => $fields)
        <details class="ve-group" @if($loop->first) open @endif>
            <summary>{{ $groupName }}</summary>
            <div class="ve-group__body">
                @foreach($fields as $name => $field)
                <label class="ve-field">
                    <span class="ve-field__top">
                        <span>{{ $field['label'] }}</span>
                        <small class="ve-count" data-ve-count></small>
                    </span>

                    @if($field['type'] === 'textarea')
                        <textarea
                            name="{{ $name }}"
                            rows="{{ max(2, min(5, $field['lines'])) }}"
                            maxlength="{{ $field['max'] }}"
                            data-ve-field
                            data-recommended="{{ $field['recommended'] }}"
                            data-max="{{ $field['max'] }}"
                            @required($field['required'])
                        >{{ old($name, $content[$name] ?? '') }}</textarea>
                    @else
                        <input
                            type="{{ $field['type'] }}"
                            name="{{ $name }}"
                            value="{{ old($name, $content[$name] ?? '') }}"
                            maxlength="{{ $field['max'] }}"
                            data-ve-field
                            data-recommended="{{ $field['recommended'] }}"
                            data-max="{{ $field['max'] }}"
                            @required($field['required'])
                        >
                    @endif

                    <small class="ve-fit" data-ve-fit></small>
                </label>
                @endforeach

                @if($groupName === 'Hero / First Screen')
                <section class="ve-image">
                    <h3>{{ $imageRule['label'] }}</h3>
                    <p>Frame: {{ $imageRule['frame'] }} · minimum {{ $imageRule['min_width'] }} x {{ $imageRule['min_height'] }} px · maximum {{ number_format($imageRule['max_kb'] / 1024, 0) }} MB · {{ $imageRule['formats'] }}</p>

                    <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" data-ve-image-input>
                    <div class="ve-file-meta" data-ve-file-meta>No new image selected. Portrait or tall images are allowed if they meet the minimum dimensions; reposition them inside the locked frame below.</div>

                    <label class="ve-slider">
                        <span>Horizontal</span>
                        <input type="range" name="hero_image_focus_x" min="0" max="100" step="1" value="{{ old('hero_image_focus_x', $content['hero_image_focus_x'] ?? 50) }}">
                        <strong data-ve-slider-value>{{ old('hero_image_focus_x', $content['hero_image_focus_x'] ?? 50) }}</strong>
                    </label>

                    <label class="ve-slider">
                        <span>Vertical</span>
                        <input type="range" name="hero_image_focus_y" min="0" max="100" step="1" value="{{ old('hero_image_focus_y', $content['hero_image_focus_y'] ?? 50) }}">
                        <strong data-ve-slider-value>{{ old('hero_image_focus_y', $content['hero_image_focus_y'] ?? 50) }}</strong>
                    </label>

                    <label class="ve-slider">
                        <span>Zoom</span>
                        <input type="range" name="hero_image_zoom" min="1" max="2" step="0.01" value="{{ old('hero_image_zoom', $content['hero_image_zoom'] ?? 1) }}">
                        <strong data-ve-slider-value>{{ old('hero_image_zoom', $content['hero_image_zoom'] ?? 1) }}</strong>
                    </label>

                    <label class="ve-consent">
                        <input type="checkbox" name="hero_image_authorized" value="1">
                        <span><strong>Approved for website publication.</strong> Required only when uploading a new photograph.</span>
                    </label>
                </section>
                @endif
            </div>
        </details>
        @endforeach

        <div class="ve-save">
            <button type="submit">Save Homepage Content</button>
            <p class="ve-help">Saving changes content only. The visual template remains locked.</p>
        </div>
    </form>

    <section class="ve-stage">
        <div class="ve-toolbar">
            <div>
                <strong>Live page</strong>
                <small class="ve-help">Click editable text directly in the page.</small>
            </div>
            <div class="ve-toolbar__devices" aria-label="Preview width">
                <button type="button" class="is-active" data-ve-device="desktop">Desktop</button>
                <button type="button" data-ve-device="tablet">Tablet</button>
                <button type="button" data-ve-device="phone">Phone</button>
            </div>
        </div>
        <div class="ve-frame-wrap" data-ve-frame-wrap data-device="desktop">
            <iframe
                class="ve-frame"
                src="{{ route('home', ['visual_preview' => 1]) }}"
                title="Live homepage visual editor preview"
                data-ve-frame
            ></iframe>
        </div>
    </section>
</section>
@endsection
