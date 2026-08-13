@extends('admin.layouts.app', ['title' => 'Photos'])

@section('content')
<section class="cm-page-head">
    <div><span class="cm-eyebrow">Approved photographs</span><h1>Photos</h1><p>Use only school-authorized images with appropriate permission for identifiable children.</p></div>
    <a href="{{ route('admin.gallery.create') }}" class="cm-button cm-button--primary">+ Upload Photo</a>
</section>

<div class="cm-photo-grid">
@forelse($items as $item)
    <article class="cm-photo-card">
        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->alt_text }}">
        <div class="cm-photo-card__body">
            <span @class(['is-live' => $item->is_published && $item->consent_confirmed_at])>{{ $item->is_published && $item->consent_confirmed_at ? 'Published' : 'Draft' }}</span>
            <h2>{{ $item->title }}</h2>
            <p>{{ $item->category }}</p>
            <div>
                <a href="{{ route('admin.gallery.edit', $item) }}">Edit</a>
                <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" data-cm-confirm="Delete this gallery photo and its stored image?">
                    @csrf @method('DELETE')
                    <button type="submit">Delete</button>
                </form>
            </div>
        </div>
    </article>
@empty
    <div class="cm-empty cm-empty--large">No gallery photos yet. Click â€œUpload Photoâ€ when an approved image is ready.</div>
@endforelse
</div>
<div class="cm-pagination">{{ $items->links() }}</div>
@endsection