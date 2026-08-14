@extends('admin.layouts.app', ['title' => $item->exists ? 'Edit Facebook Video' : 'Add Facebook Video'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.facebook-media.index') }}">&larr; Live &amp; Videos</a>
        <span class="cm-eyebrow">External Media</span>
        <h1>{{ $item->exists ? 'Edit Facebook media' : 'Add Facebook media' }}</h1>
        <p>Paste the public Facebook video or Facebook Live URL. The preview below shows the Facebook player visitors will see. Do not paste iframe or script code.</p>
    </div>
</section>

<form class="cm-panel cm-panel--wide p22-admin-form" method="POST" action="{{ $item->exists ? route('admin.facebook-media.update', $item) : route('admin.facebook-media.store') }}">
    @csrf
    @if($item->exists) @method('PATCH') @endif

    <div class="p22-admin-form__grid">
        <label class="cm-field p22-span-2">
            <span>Title</span>
            <input type="text" name="title" maxlength="180" required value="{{ old('title', $item->title) }}">
        </label>

        <label class="cm-field">
            <span>Media type</span>
            <select name="media_type" required>
                @foreach(\App\Models\FacebookMediaItem::MEDIA_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('media_type', $item->media_type ?: 'video') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="cm-field">
            <span>Reference / scheduled time <small>optional</small></span>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $item->starts_at?->format('Y-m-d\TH:i')) }}">
        </label>

        <label class="cm-field p22-span-2">
            <span>Public Facebook video / live URL</span>
            <input
                type="url"
                name="facebook_url"
                required
                maxlength="2048"
                placeholder="https://www.facebook.com/.../videos/..."
                value="{{ old('facebook_url', $item->facebook_url) }}"
                data-facebook-url-input
            >
            <small>Accepted hosts: facebook.com and fb.watch over HTTPS. Visitors see the Facebook preview/player instead of a raw URL.</small>
        </label>

        <div class="p22-admin-preview p22-span-2" data-facebook-admin-preview>
            <div class="p22-admin-preview__head">
                <div>
                    <strong>Facebook Preview</strong>
                    <small>Paste or change the link above to preview the thumbnail/player.</small>
                </div>
                <span data-facebook-preview-status>Waiting for a Facebook link</span>
            </div>
            <div class="p22-admin-preview__frame" data-facebook-preview-frame>
                <div class="p22-admin-preview__empty">
                    <span aria-hidden="true">&#9654;</span>
                    <strong>No preview yet</strong>
                    <small>A valid public Facebook video or live URL will appear here.</small>
                </div>
            </div>
        </div>

        <label class="cm-field p22-span-2">
            <span>Description <small>optional</small></span>
            <textarea name="description" rows="5" maxlength="3000">{{ old('description', $item->description) }}</textarea>
        </label>

        @if(auth()->user()->isTeacher())
            <input type="hidden" name="status" value="draft">
            <div class="p22-admin-draft p22-span-2">
                <strong>Teacher workflow:</strong> this entry remains Draft until a Principal or Super Admin reviews and publishes it.
            </div>
        @else
            <label class="cm-field">
                <span>Status</span>
                <select name="status" required>
                    @foreach(\App\Models\FacebookMediaItem::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $item->status ?: 'draft') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="p22-check">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $item->is_featured))>
                <span><strong>Feature this video</strong><small>Featured published items appear first.</small></span>
            </label>

            <label class="p22-check p22-span-2">
                <input type="checkbox" name="public_confirmed" value="1">
                <span>
                    <strong>I confirm this Facebook video is Public and approved for embedding.</strong>
                    <small>Required whenever you save an item with Published status. If Facebook later makes the post non-public, the embedded player will stop being available.</small>
                </span>
            </label>
        @endif
    </div>

    <div class="p22-admin-form__actions">
        <button class="cm-button cm-button--primary" type="submit">{{ $item->exists ? 'Save Changes' : 'Save Facebook Media' }}</button>
        <a class="cm-button cm-button--secondary" href="{{ route('admin.facebook-media.index') }}">Cancel</a>
    </div>
</form>
@endsection
