@extends('admin.layouts.app', ['title' => 'Visual Homepage Editor'])

@section('content')
@php
    $groups = collect($schema)->groupBy('group', true);
@endphp

<section class="ve-shell" data-ve-editor>
    <form method="POST" enctype="multipart/form-data" action="{{ route('admin.website-content.update') }}" class="ve-panel" data-ve-form>
        @csrf
        @method('PATCH')

        <div class="ve-panel__head">
            <span class="ve-lock ve59-pro-badge">LOCKED DESIGN / PRO RESPONSIVE EDITOR</span>
            <h1>Professional Homepage Editor</h1>
            <p>Select text directly in the live page, edit content, and use the responsive inspector to tune typography and text frames for desktop, tablet, and phone. Dashboard and operational school-management pages stay outside this editor.</p>
            <div class="ve58-bar" aria-label="Editor recovery tools">
                <button type="button" data-ve-undo title="Ctrl+Z">Undo</button>
                <button type="button" data-ve-redo title="Ctrl+Y">Redo</button>
                <button type="button" data-ve-save-draft>Save Draft</button>
                <details class="ve58-menu"><summary>Reset Page</summary><div class="ve58-menu-card"><button type="button" data-ve-reset-unsaved>Reset unsaved changes</button><button type="submit" form="ve58-reset-original" onclick="return confirm('Restore the original approved homepage? The current live state will be preserved first.');">Restore original page</button></div></details>
                <span class="ve58-status" data-ve-premium-status>Saved</span>
            </div>
            <div class="ve58-draft" data-ve-draft-banner hidden><span><strong>Recovery draft found.</strong> Unsaved work exists on this device.</span><span><button type="button" data-ve-restore-draft>Restore Draft</button> <button type="button" data-ve-discard-draft>Discard</button></span></div>
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

        <div data-ve-hidden-host>
            @foreach($hiddenFields as $hiddenField)<input type="hidden" name="hidden_fields[]" value="{{ $hiddenField }}" data-ve-hidden-initial>@endforeach
        </div>
        <textarea name="style_overrides" data-ve-style-overrides hidden>{{ json_encode($styleOverrides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</textarea>
        <section class="ve58-drawer"><h2>Hidden Elements</h2><p>Hide is reversible. Nothing here is permanently deleted.</p><div data-ve-hidden-list></div><button type="button" data-ve-restore-all>Restore All Hidden Elements</button></section>
        <section class="ve58-drawer"><h2>Revision History</h2><p>Every Publish creates a recoverable server revision.</p>
            @forelse($revisions as $revision)
                <div class="ve58-revision-row"><span><strong>{{ ucfirst(str_replace('_', ' ', $revision['reason'])) }}</strong><small>{{ $revision['actor'] }} - {{ $revision['saved_at'] }}</small></span><span class="ve58-revision-actions"><button type="button" data-ve-preview-revision data-revision-key="{{ $revision['key'] }}">Preview</button><button type="submit" form="ve58-revision-{{ $loop->index }}" onclick="return confirm('Restore this revision? The current live state will be preserved first.');">Restore</button></span></div>
            @empty<p>No revisions yet. The first Publish will create one.</p>@endforelse
        </section>

        @foreach($groups as $groupName => $fields)
        <details class="ve-group" @if($loop->first) open @endif>
            <summary>{{ $groupName }}</summary>
            <div class="ve-group__body">
                @foreach($fields as $name => $field)
                <label class="ve-field">
                    <span class="ve-field__top">
                        <span data-ve-field-label>{{ $field['label'] }}</span>
                        <span class="ve58-field-tools"><small class="ve-count" data-ve-count></small><button type="button" class="ve58-hide" data-ve-hide data-field="{{ $name }}">Hide</button></span>
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
            <button type="submit">Publish Changes</button>
            <p class="ve-help">Publishing creates a server revision. The visual template remains locked.</p>
        </div>
    </form>

    <section class="ve-stage">
        <div class="ve-toolbar">
            <div>
                <strong>Live responsive page</strong>
                <small class="ve-help">Click editable text to select it. Fit health is measured from the real preview.</small>
            </div>
            <div class="ve-toolbar__devices" aria-label="Preview width">
                <button type="button" class="is-active" data-ve-device="desktop">Desktop</button>
                <button type="button" data-ve-device="tablet">Tablet</button>
                <button type="button" data-ve-device="phone">Phone</button>
            </div>
        </div>

        <div class="ve59-workspace">
            <div class="ve-frame-wrap" data-ve-frame-wrap data-device="desktop">
                <iframe
                    class="ve-frame"
                    src="{{ route('home', ['visual_preview' => 1]) }}"
                    title="Live homepage visual editor preview"
                    data-ve-frame
                ></iframe>
            </div>

            <aside class="ve59-inspector" data-ve-pro-inspector aria-label="Professional responsive inspector">
                <div class="ve59-inspector__head">
                    <span>PRO INSPECTOR</span>
                    <strong data-ve-selected-label>Select an element</strong>
                    <small data-ve-selected-field>Click text in the preview or focus a content field.</small>
                </div>

                <div class="ve59-fit-health" data-ve-fit-health>
                    <span class="ve59-fit-dot" aria-hidden="true"></span>
                    <strong>Fit health ready</strong>
                    <small>Responsive overflow and awkward wrapping are monitored in the preview.</small>
                </div>

                <div class="ve59-smart-actions">
                    <button type="button" data-ve-auto-fit>Smart Auto Fit</button>
                    <button type="button" data-ve-auto-fit-all>Fit All Alerts</button>
                    <button type="button" data-ve-reset-style>Reset Device Style</button>
                    <button type="button" data-ve-reset-style-all>Reset All Styles</button>
                </div>

                <div class="ve59-scope" aria-label="Responsive style scope">
                    <button type="button" class="is-active" data-ve-style-scope="base">Desktop Base</button>
                    <button type="button" data-ve-style-scope="tablet">Tablet</button>
                    <button type="button" data-ve-style-scope="phone">Phone</button>
                </div>

                <fieldset class="ve59-controls" data-ve-pro-controls disabled>
                    <legend>Typography</legend>

                    <label>
                        <span>Font size <output data-ve-pro-output="font_size">Theme</output></span>
                        <input type="range" min="10" max="72" step="0.5" value="16" data-ve-pro-control data-prop="font_size">
                    </label>

                    <label>
                        <span>Line height <output data-ve-pro-output="line_height">Theme</output></span>
                        <input type="range" min="0.8" max="2.2" step="0.05" value="1.2" data-ve-pro-control data-prop="line_height">
                    </label>

                    <label>
                        <span>Letter spacing <output data-ve-pro-output="letter_spacing">Theme</output></span>
                        <input type="range" min="-2" max="6" step="0.1" value="0" data-ve-pro-control data-prop="letter_spacing">
                    </label>

                    <label>
                        <span>Weight</span>
                        <select data-ve-pro-control data-prop="font_weight">
                            <option value="">Theme default</option>
                            <option value="400">Regular 400</option>
                            <option value="500">Medium 500</option>
                            <option value="600">Semi-bold 600</option>
                            <option value="700">Bold 700</option>
                            <option value="800">Extra-bold 800</option>
                            <option value="900">Black 900</option>
                        </select>
                    </label>

                    <label>
                        <span>Alignment</span>
                        <select data-ve-pro-control data-prop="text_align">
                            <option value="">Theme default</option>
                            <option value="left">Left</option>
                            <option value="center">Center</option>
                            <option value="right">Right</option>
                        </select>
                    </label>

                    <label>
                        <span>Text flow</span>
                        <select data-ve-pro-control data-prop="flow">
                            <option value="">Theme default</option>
                            <option value="normal">Responsive wrap</option>
                            <option value="nowrap">Keep on one line</option>
                            <option value="balance">Balanced heading</option>
                        </select>
                    </label>

                    <legend>Text Frame</legend>

                    <label>
                        <span>Maximum width <output data-ve-pro-output="max_width">Auto</output></span>
                        <input type="range" min="0" max="1200" step="10" value="0" data-ve-pro-control data-prop="max_width">
                    </label>

                    <label>
                        <span>Minimum height <output data-ve-pro-output="min_height">Auto</output></span>
                        <input type="range" min="0" max="500" step="5" value="0" data-ve-pro-control data-prop="min_height">
                    </label>

                    <label>
                        <span>Horizontal padding <output data-ve-pro-output="padding_x">Theme</output></span>
                        <input type="range" min="0" max="80" step="1" value="0" data-ve-pro-control data-prop="padding_x">
                    </label>

                    <label>
                        <span>Vertical padding <output data-ve-pro-output="padding_y">Theme</output></span>
                        <input type="range" min="0" max="80" step="1" value="0" data-ve-pro-control data-prop="padding_y">
                    </label>
                </fieldset>

                <p class="ve59-inspector__note">Professional controls are allowlisted and range-limited. Navigation, code, permissions, Dashboard, and operational school records cannot be edited here.</p>
            </aside>
        </div>
    </section>
    <div class="ve58-toast" data-ve-premium-toast hidden></div>
    <script type="application/json" data-ve-revisions-data>@json($revisions)</script>
</section>
<form id="ve58-reset-original" method="POST" action="{{ route('admin.website-content.reset-original') }}" hidden>@csrf @method('PATCH')</form>
@foreach($revisions as $revision)<form id="ve58-revision-{{ $loop->index }}" method="POST" action="{{ route('admin.website-content.revisions.restore', $revision['key']) }}" hidden>@csrf @method('PATCH')</form>@endforeach
@endsection
