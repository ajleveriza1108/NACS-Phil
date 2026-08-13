@extends('admin.layouts.app', ['title' => 'Media Library'])
@section('content')
<section class="cm-page-head"><div><span class="cm-eyebrow">Reusable images</span><h1>Media Library</h1><p>Central image storage with rights, consent, alt text, credit, and deletion protection.</p></div><a class="cm-button cm-button--primary" href="{{ route('admin.media.create') }}">Upload Image</a></section>
<div class="p12-grid">
@forelse($assets as $asset)
<article class="p12-card">
<img src="{{ Storage::url($asset->file_path) }}" alt="{{ $asset->alt_text }}" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:12px">
<h2>{{ $asset->title }}</h2>
<p>{{ $asset->category ?: 'Uncategorized' }} &middot; {{ $asset->original_name }}</p>
<p><span class="p12-badge {{ $asset->rights_confirmed_at ? 'p12-badge--good' : 'p12-badge--warn' }}">Rights {{ $asset->rights_confirmed_at ? 'confirmed' : 'missing' }}</span>
@if($asset->consent_confirmed_at)<span class="p12-badge p12-badge--good">Consent confirmed</span>@endif</p>
@if(!auth()->user()->isTeacher())<form method="POST" action="{{ route('admin.media.destroy',$asset) }}">@csrf @method('DELETE')<button class="cm-button cm-button--secondary">Delete if unused</button></form>@endif
</article>
@empty<div class="cm-empty">No media assets uploaded.</div>@endforelse
</div>
{{ $assets->links() }}
@endsection
