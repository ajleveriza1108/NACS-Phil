@extends('layouts.public', ['title' => 'Live & Videos'])

@section('meta_description', 'Watch public Facebook videos and school livestreams shared by NACS-Phil without storing video files on the school website.')

@section('content')
<section class="p22-media-hero">
    <div class="nacs11-shell p22-media-hero__inner">
        <div>
            <span class="p22-media-kicker">School Media</span>
            <h1>Live &amp; Videos</h1>
            <p>Watch public school videos and Facebook Live broadcasts from one convenient page. Video files remain hosted and streamed by Facebook instead of taking up space on the school website.</p>
        </div>
        <div class="p22-media-hero__mark" aria-hidden="true">
            <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="">
        </div>
    </div>
</section>

<section class="p22-media-section">
    <div class="nacs11-shell">
        <div class="p22-media-notice">
            <div>
                <strong>Facebook-hosted preview and playback</strong>
                <p>The preview image and playback controls are provided by Facebook. Pressing Play keeps the visitor on the NACS-Phil page while Facebook streams the actual video. Facebook's own privacy and cookie terms may apply to the embedded player.</p>
            </div>
            <a href="{{ route('privacy') }}">Read our Privacy page <span aria-hidden="true">&rarr;</span></a>
        </div>

        <div class="p22-media-grid">
            @forelse($items as $item)
                <article class="p22-media-card {{ $item->is_featured ? 'is-featured' : '' }}">
                    <div class="p22-media-card__meta">
                        <span>{{ $item->mediaTypeLabel() }}</span>
                        @if($item->is_featured)<b>Featured</b>@endif
                        @if($item->starts_at)<small>{{ $item->starts_at->format('M j, Y · g:i A') }}</small>@endif
                    </div>

                    <h2>{{ $item->title }}</h2>
                    @if($item->description)<p>{{ $item->description }}</p>@endif

                    @if($item->embedUrl())
                        <div class="p22-player">
                            <iframe
                                src="{{ $item->embedUrl() }}"
                                title="{{ $item->title }}"
                                loading="lazy"
                                allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                                allowfullscreen
                                referrerpolicy="strict-origin-when-cross-origin"
                            ></iframe>
                        </div>
                    @else
                        <div class="p22-player p22-player--unavailable">
                            <div>
                                <span aria-hidden="true">&#9654;</span>
                                <strong>Facebook player unavailable</strong>
                                <small>Open the public video directly on Facebook.</small>
                            </div>
                        </div>
                    @endif

                    <div class="p22-media-card__footer">
                        <span>Hosted by Facebook &middot; not stored on NACS-Phil</span>
                        <a href="{{ $item->facebook_url }}" target="_blank" rel="noopener noreferrer">
                            Watch on Facebook <span aria-hidden="true">&nearr;</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="p22-media-empty">
                    <span aria-hidden="true">&#9654;</span>
                    <h2>No public videos yet</h2>
                    <p>Approved recordings and Facebook Live links will appear here when school staff publish them.</p>
                    <a class="nacs11-button nacs11-button--primary" href="{{ route('events.index') }}">View School Events</a>
                </div>
            @endforelse
        </div>

        @if(method_exists($items, 'links'))
            <div class="p22-media-pages">{{ $items->links() }}</div>
        @endif
    </div>
</section>
@endsection
