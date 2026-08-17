@extends('layouts.site-current', ['title' => 'School Documents', 'bodyClass' => 'nacs11-public nacs-current-page nacs-current-page--public', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'public', 'useVite' => true])
@section('content')
<section class="nacs12-hero">
    <div class="nacs11-shell">
        <span class="nacs12-kicker">School Resources</span>
        <h1>Documents &amp; Downloads</h1>
        <p>Public school forms, calendars, handbooks, requirements, and other approved resources.</p>
    </div>
</section>

<section class="nacs12-section">
    <div class="nacs11-shell">
        @if($categories->isNotEmpty())
        <div class="nacs12-filters">
            <a href="{{ route('documents.index') }}" class="{{ $category === '' ? 'is-active' : '' }}">All</a>
            @foreach($categories as $item)
                <a href="{{ route('documents.index', ['category' => $item]) }}" class="{{ $category === $item ? 'is-active' : '' }}">{{ $item }}</a>
            @endforeach
        </div>
        @endif

        <div class="nacs12-list">
            @forelse($documents as $document)
                <article class="nacs12-resource">
                    <div>
                        <span class="nacs12-chip">{{ $document->category }}</span>
                        <h2>{{ $document->title }}</h2>
                        @if($document->description)<p>{{ $document->description }}</p>@endif
                        <small>{{ $document->school_year ?: 'Current resource' }} &middot; {{ $document->formattedSize() }}</small>
                    </div>
                    <a class="nacs11-button nacs11-button--primary" href="{{ route('documents.download', $document) }}">Download</a>
                </article>
            @empty
                <div class="nacs12-empty">No public documents are available yet.</div>
            @endforelse
        </div>

        {{ $documents->links() }}
    </div>
</section>
@endsection
