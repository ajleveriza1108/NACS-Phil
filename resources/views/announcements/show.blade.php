@php($newsContent = \App\Models\SiteContent::valuesFor('news', \App\Support\NewsContent::defaults()))
@php($announcementBody = $announcement->body ?? $announcement->content ?? $announcement->details ?? $announcement->description ?? '')
@php($newsType = $announcement->type ?? 'Announcement')
@extends('layouts.site-current', ['bodyClass' => 'news-phase5 nacs-current-page nacs-current-page--news', 'mainId' => 'news-main', 'mainClass' => '', 'assetBundle' => 'news', 'useVite' => false])
@section('title', $announcement->title)
@section('meta_description', $announcement->excerpt ?? 'School announcement from NACS-Phil.')

@section('content')
<section class="news-detail-hero">
    <div class="news-shell news-detail-hero__inner">
        <div data-news-reveal>
            <a href="{{ route('announcements.index') }}" class="news-back-link">&larr; {{ $newsContent['detail_back_label'] }}</a>
            <div class="news-detail-hero__meta">
                <span>{{ $newsType }}</span>
                <i aria-hidden="true"></i>
                <span>{{ optional($announcement->published_at)->format('F j, Y') ?? 'School Update' }}</span>
                @if(!empty($announcement->is_featured))
                    <b>Featured</b>
                @endif
            </div>
            <h1>{{ $announcement->title }}</h1>
            @if(!empty($announcement->excerpt))
                <p>{{ $announcement->excerpt }}</p>
            @endif
        </div>
    </div>
</section>

<section class="news-detail-section">
    <div class="news-shell news-detail-grid">
        <article class="news-article" data-news-reveal>
            @if(!empty($announcementBody))
                <div class="news-article__content">{!! nl2br(e($announcementBody)) !!}</div>
            @elseif(!empty($announcement->excerpt))
                <div class="news-article__content">{!! nl2br(e($announcement->excerpt)) !!}</div>
            @else
                <div class="news-article__content"><p>This announcement does not currently contain additional public details.</p></div>
            @endif
        </article>

        <aside class="news-detail-aside" data-news-reveal>
            <div class="news-detail-aside__card">
                <span>Published Update</span>
                <strong>{{ config('nacs.short_name') }}</strong>
                <p>{{ optional($announcement->published_at)->format('F j, Y') ?? 'Date not specified' }}</p>
                <a href="{{ route('announcements.index') }}">View all news &rarr;</a>
            </div>
        </aside>
    </div>
</section>

<section class="news-section news-section--cta">
    <div class="news-shell">
        <div class="news-final-cta" data-news-reveal>
            <div>
                <span class="news-kicker news-kicker--gold">School Information</span>
                <h2>{{ $newsContent['detail_footer_heading'] }}</h2>
                <p>{{ $newsContent['detail_footer_text'] }}</p>
            </div>
            <a class="news-button news-button--gold" href="{{ route('contact') }}">{{ $newsContent['detail_contact_button'] }} <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>
@endsection
