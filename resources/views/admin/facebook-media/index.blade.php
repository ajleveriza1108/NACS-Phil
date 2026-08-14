@extends('admin.layouts.app', ['title' => 'Live & Videos'])

@section('content')
<section class="cm-page-head">
    <div>
        <a class="cm-back-link" href="{{ route('admin.dashboard') }}">&larr; School Manager</a>
        <span class="cm-eyebrow">External Media</span>
        <h1>Facebook Live &amp; Videos</h1>
        <p>Publish links to public Facebook videos or livestreams without uploading large video files to this website.</p>
    </div>
    <div class="cm-page-head__actions">
        <a class="cm-button cm-button--primary" href="{{ route('admin.facebook-media.create') }}">Add Facebook Video</a>
        <a class="cm-button cm-button--secondary" href="{{ route('media.index') }}" target="_blank" rel="noopener">View Public Page &nearr;</a>
    </div>
</section>

<div class="p22-admin-note">
    <strong>No video upload is required.</strong>
    Store only the public Facebook link. NACS-Phil generates the player safely and visitors load Facebook only when they choose to play it.
    @if(auth()->user()->isTeacher())
        <br><strong>Teacher accounts save entries as Draft.</strong> A Principal or Super Admin publishes them.
    @endif
</div>

<section class="cm-panel cm-panel--wide">
    <div class="cm-panel__head">
        <div><span class="cm-eyebrow">Media Hub</span><h2>Facebook links</h2></div>
        <span>{{ $items->total() }} item(s)</span>
    </div>

    <div class="p22-admin-list">
        @forelse($items as $item)
            <article>
                <div class="p22-admin-list__main">
                    <div class="p22-admin-badges">
                        <span>{{ $item->mediaTypeLabel() }}</span>
                        <span class="is-{{ $item->status }}">{{ \App\Models\FacebookMediaItem::STATUSES[$item->status] ?? ucfirst($item->status) }}</span>
                        @if($item->is_featured)<span class="is-featured">Featured</span>@endif
                    </div>
                    <strong>{{ $item->title }}</strong>
                    <small>{{ $item->facebook_url }}</small>
                    @if($item->starts_at)<small>Scheduled/reference time: {{ $item->starts_at->format('M j, Y g:i A') }}</small>@endif
                </div>
                <div class="p22-admin-actions">
                    <a class="cm-button cm-button--secondary" href="{{ route('admin.facebook-media.edit', $item) }}">Edit</a>
                    <a class="cm-button cm-button--secondary" href="{{ $item->facebook_url }}" target="_blank" rel="noopener noreferrer">Facebook &nearr;</a>
                    @unless(auth()->user()->isTeacher())
                        <form method="POST" action="{{ route('admin.facebook-media.destroy', $item) }}" onsubmit="return confirm('Move this Facebook media link to Safe Trash?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p9b-danger-button">Trash</button>
                        </form>
                    @endunless
                </div>
            </article>
        @empty
            <div class="cm-empty">No Facebook video or livestream links have been added yet.</div>
        @endforelse
    </div>

    <div>{{ $items->links() }}</div>
</section>
@endsection
