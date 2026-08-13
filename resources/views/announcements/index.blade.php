@php($newsContent = \App\Models\SiteContent::valuesFor('news', \App\Support\NewsContent::defaults()))
@extends('layouts.news-phase5')

@section('title', 'News & Announcements')

@section('content')
<section class="news-hero">
    <div class="news-hero__grid" aria-hidden="true"></div>
    <div class="news-shell news-hero__inner">
        <div class="news-hero__copy" data-news-reveal>
            <span class="news-pill">{{ $newsContent['hero_badge'] }}</span>
            <h1>{{ $newsContent['hero_heading'] }} <span>{{ $newsContent['hero_highlight'] }}</span></h1>
            <p>{{ $newsContent['hero_lead'] }}</p>
            <div class="news-hero__actions">
                <a class="news-button news-button--primary" href="#latest-news">Read Latest Updates <span aria-hidden="true">&darr;</span></a>
                <a class="news-button news-button--secondary" href="{{ route('events.index') }}">View Events <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div>
        <div class="news-hero__visual" data-news-reveal>
            <div class="news-hero__visual-frame">
                <img src="{{ asset('assets/phase5-news/news-visual.svg') }}" alt="Abstract illustration of school announcements, news cards, and community updates.">
                <div class="news-hero__visual-badge"><span aria-hidden="true"></span><div><strong>School Updates</strong><small>Published by authorized staff</small></div></div>
            </div>
        </div>
    </div>
</section>

<section id="latest-news" class="news-section">
    <div class="news-shell">
        <div class="news-section-head" data-news-reveal>
            <div><span class="news-kicker">Latest Updates</span><h2>{{ $newsContent['listing_heading'] }}</h2></div>
            <p>{{ $newsContent['listing_text'] }}</p>
        </div>

        <div class="news-grid">
            @forelse($announcements as $announcement)
                @php($newsType = $announcement->type ?? 'Announcement')
                <article class="news-card {{ !empty($announcement->is_featured) ? 'is-featured' : '' }}" data-news-reveal>
                    <a href="{{ route('announcements.show', $announcement) }}" class="news-card__visual" aria-label="Read {{ $announcement->title }}">
                        <span class="news-card__type">{{ $newsType }}</span>
                        @if(!empty($announcement->is_featured))
                            <span class="news-card__featured">Featured</span>
                        @endif
                        <div class="news-card__letter" aria-hidden="true">{{ strtoupper(substr((string) $announcement->title, 0, 1)) }}</div>
                    </a>
                    <div class="news-card__body">
                        <div class="news-card__meta">
                            <span>{{ optional($announcement->published_at)->format('M j, Y') ?? 'School Update' }}</span>
                            <i aria-hidden="true"></i>
                            <span>{{ $newsType }}</span>
                        </div>
                        <h3><a href="{{ route('announcements.show', $announcement) }}">{{ $announcement->title }}</a></h3>
                        @if(!empty($announcement->excerpt))
                            <p>{{ $announcement->excerpt }}</p>
                        @endif
                        <a class="news-card__read" href="{{ route('announcements.show', $announcement) }}">Read announcement <span aria-hidden="true">&rarr;</span></a>
                    </div>
                </article>
            @empty
                <div class="news-empty" data-news-reveal>
                    <span class="news-empty__icon" aria-hidden="true">N</span>
                    <h3>{{ $newsContent['empty_heading'] }}</h3>
                    <p>{{ $newsContent['empty_text'] }}</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($announcements, 'links'))
            <div class="news-pagination" data-news-reveal>{{ $announcements->links() }}</div>
        @endif
    </div>
</section>
@endsection