@php($localPresentationPreview = app()->environment('local') && is_file(storage_path('app/.nacs-presentation-preview')))
@extends('layouts.site-current', ['title' => 'Media Hub', 'bodyClass' => 'nacs11-public nacs-current-page nacs-current-page--public', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'public', 'useVite' => true])
@section('meta_description', 'Explore approved NACS-Phil school photos, recorded Facebook videos, and public Facebook Live broadcasts from one media hub.')

@section('content')
<section class="p22-media-hero mh-hero">
    <div class="nacs11-shell p22-media-hero__inner">
        <div>
            <span class="p22-media-kicker">School Life &amp; Media</span>
            <h1>Media Hub</h1>
            <p>{{ $localPresentationPreview ? 'Local presentation photos are shown here for school review and still require school approval before production.' : 'See approved school photographs, recorded videos, and public livestreams in one place. Photos are managed by NACS-Phil, while Facebook videos and live broadcasts remain hosted and streamed by Facebook.' }}</p>
        </div>
        <div class="p22-media-hero__mark" aria-hidden="true">
            <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="">
        </div>
    </div>
</section>

<section class="p22-media-section mh-section">
    <div class="nacs11-shell">
        <nav class="mh-tabs" aria-label="Media type">
            <a href="{{ route('media.index') }}" @class(['is-active' => $activeType === 'all'])>All Media</a>
            <a href="{{ route('media.index', ['type' => 'photos']) }}" @class(['is-active' => $activeType === 'photos'])>Photos</a>
            <a href="{{ route('media.index', ['type' => 'videos']) }}" @class(['is-active' => $activeType === 'videos'])>Videos</a>
            <a href="{{ route('media.index', ['type' => 'live']) }}" @class(['is-active' => $activeType === 'live'])>Live</a>
        </nav>

        <div class="mh-stats" aria-label="Published media summary">
            <a href="{{ route('gallery.index') }}"><span aria-hidden="true">&#9638;</span><strong>{{ $photoCount }}</strong><small>{{ $localPresentationPreview ? 'Preview Photos' : 'Approved Photos' }}</small></a>
            <a href="{{ route('media.index', ['type' => 'videos']) }}"><span aria-hidden="true">&#9654;</span><strong>{{ $videoCount }}</strong><small>Recorded Videos</small></a>
            <a href="{{ route('media.index', ['type' => 'live']) }}"><span aria-hidden="true">&#9679;</span><strong>{{ $liveCount }}</strong><small>Live Broadcasts</small></a>
        </div>

        @if(in_array($activeType, ['all', 'photos'], true))
            <section class="mh-block" aria-labelledby="mh-photos-heading">
                <div class="mh-section-head">
                    <div>
                        <span class="p22-media-kicker">Photo Gallery</span>
                        <h2 id="mh-photos-heading">Life at NACS-Phil</h2>
                        <p>{{ $localPresentationPreview ? 'These local preview photographs are for the school-president presentation only; school approval is still required before production publication.' : 'Only school-authorized photographs with confirmed publishing consent appear publicly.' }}</p>
                    </div>
                    <a class="nacs11-button nacs11-button--primary" href="{{ route('gallery.index') }}">Open Full Gallery <span aria-hidden="true">&rarr;</span></a>
                </div>

                @if($photos->isNotEmpty())
                    <div class="mh-photo-grid">
                        @foreach($photos as $photo)
                            <a class="mh-photo-card" href="{{ route('gallery.index') }}">
                                <span class="mh-photo-card__image"><img src="{{ Storage::url($photo->image_path) }}" alt="{{ $photo->alt_text }}" loading="lazy"></span>
                                <span class="mh-photo-card__copy">
                                    <small>{{ $photo->category }}</small>
                                    <strong>{{ $photo->title }}</strong>
                                    @if($photo->taken_at)<span>{{ $photo->taken_at->format('M Y') }}</span>@endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="mh-empty"><span aria-hidden="true">&#9638;</span><h3>No approved photos yet</h3><p>Published Gallery photographs will appear here automatically.</p></div>
                @endif
            </section>
        @endif

        @if(in_array($activeType, ['all', 'videos', 'live'], true))
            <section class="mh-block mh-block--video" aria-labelledby="mh-video-heading">
                <div class="mh-section-head">
                    <div>
                        <span class="p22-media-kicker">{{ $activeType === 'live' ? 'Livestreams' : ($activeType === 'videos' ? 'Recorded Video' : 'Videos & Livestreams') }}</span>
                        <h2 id="mh-video-heading">{{ $activeType === 'live' ? 'Facebook Live' : ($activeType === 'videos' ? 'Recorded Videos' : 'Watch & Join') }}</h2>
                        <p>Published Facebook video and Facebook Live links can be watched here without storing large video files on the school website.</p>
                    </div>
                </div>

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
                                    <iframe src="{{ $item->embedUrl() }}" title="{{ $item->title }}" loading="lazy" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                </div>
                            @else
                                <div class="p22-player p22-player--unavailable"><div><span aria-hidden="true">&#9654;</span><strong>Facebook player unavailable</strong><small>Open the public video directly on Facebook.</small></div></div>
                            @endif

                            <div class="p22-media-card__footer">
                                <span>Hosted by Facebook &middot; not stored on NACS-Phil</span>
                                <a href="{{ $item->facebook_url }}" target="_blank" rel="noopener noreferrer">Watch on Facebook <span aria-hidden="true">&nearr;</span></a>
                            </div>
                        </article>
                    @empty
                        <div class="p22-media-empty">
                            <span aria-hidden="true">&#9654;</span>
                            <h2>{{ $activeType === 'live' ? 'No public livestreams yet' : ($activeType === 'videos' ? 'No recorded videos yet' : 'No public videos yet') }}</h2>
                            <p>Approved recordings and Facebook Live links will appear here when school staff publish them.</p>
                            <a class="nacs11-button nacs11-button--primary" href="{{ route('events.index') }}">View School Events</a>
                        </div>
                    @endforelse
                </div>

                @if(method_exists($items, 'links'))
                    <div class="p22-media-pages">{{ $items->links() }}</div>
                @endif
            </section>
        @endif
    </div>
</section>
@endsection
