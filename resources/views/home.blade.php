@extends('layouts.home-phase1')

@section('content')
<section class="hero">
    <div class="hero__mesh" aria-hidden="true"></div>
    <div class="hero__glow hero__glow--one" aria-hidden="true"></div>
    <div class="hero__glow hero__glow--two" aria-hidden="true"></div>

    <div class="home-shell hero__grid">
        <div class="hero__content" data-reveal>
            <span class="eyebrow-pill">
                <span class="eyebrow-pill__cross" aria-hidden="true">+</span>
                {{ $homeContent['hero_badge'] }}
            </span>

            <h1>{{ $homeContent['hero_heading'] }} <span>{{ $homeContent['hero_highlight'] }}</span></h1>

            <p class="hero__lead">
                {{ $homeContent['hero_lead'] }}
            </p>

            <div class="hero__actions">
                <a class="button button--primary" href="{{ route('programs') }}">
                    {{ $homeContent['hero_primary_button'] }} <span aria-hidden="true">&rarr;</span>
                </a>
                <a class="button button--secondary" href="{{ route('admissions') }}">
                    {{ $homeContent['hero_secondary_button'] }} <span aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <div class="hero__trust-row" aria-label="School website priorities">
                <span><i aria-hidden="true"></i> Clear information</span>
                <span><i aria-hidden="true"></i> Family focused</span>
                <span><i aria-hidden="true"></i> Child privacy first</span>
            </div>
        </div>

        <div class="hero__visual" data-reveal>
            <div class="hero__visual-frame">
                @if(!empty($homeContent['hero_image_path']))
                    <img src="{{ Storage::url($homeContent['hero_image_path']) }}" alt="{{ $homeContent['hero_image_alt'] }}">
                @else
                    <img src="{{ asset('assets/phase1-home/campus-concept.svg') }}" alt="{{ $homeContent['hero_image_alt'] }}">
                @endif
                <div class="hero__visual-label">
                    <span class="status-dot" aria-hidden="true"></span>
                    Campus visual placeholder
                </div>
            </div>
        </div>
    </div>

    <div class="home-shell quick-links" data-reveal>
        <a href="{{ route('programs') }}" class="quick-card">
            <span class="quick-card__icon">P</span>
            <span><strong>Preschool</strong><small>Nurturing curiosity and a love for learning</small></span>
            <b aria-hidden="true">&rarr;</b>
        </a>
        <a href="{{ route('programs') }}" class="quick-card">
            <span class="quick-card__icon">E</span>
            <span><strong>Elementary</strong><small>Building strong foundations for lifelong success</small></span>
            <b aria-hidden="true">&rarr;</b>
        </a>
        <a href="{{ route('programs') }}" class="quick-card">
            <span class="quick-card__icon">JH</span>
            <span><strong>Junior High</strong><small>Preparing responsible learners for the future</small></span>
            <b aria-hidden="true">&rarr;</b>
        </a>
        <a href="{{ route('about') }}" class="quick-card">
            <span class="quick-card__icon quick-card__icon--cross">+</span>
            <span><strong>Christ-Centered</strong><small>Anchored on biblical truth and guided by love</small></span>
            <b aria-hidden="true">&rarr;</b>
        </a>
    </div>
</section>

@if($featuredAnnouncement)
<section class="home-shell feature-notice" data-reveal>
    <a href="{{ route('announcements.show', $featuredAnnouncement) }}">
        <div>
            <span class="feature-notice__label">Featured announcement</span>
            <strong>{{ $featuredAnnouncement->title }}</strong>
            @if($featuredAnnouncement->excerpt)<p>{{ $featuredAnnouncement->excerpt }}</p>@endif
        </div>
        <span class="feature-notice__action">Read update <span aria-hidden="true">&rarr;</span></span>
    </a>
</section>
@endif

<section class="home-section">
    <div class="home-shell">
        <div class="section-heading" data-reveal>
            <div>
                <span class="section-kicker">Why Choose NACS-Phil</span>
                <h2>{{ $homeContent['why_heading'] }}</h2>
            </div>
            <p>{{ $homeContent['why_intro'] }}</p>
        </div>

        <div class="reason-grid">
            <article class="reason-card" data-reveal>
                <span class="reason-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><path d="M13 10h22v8c0 7-4 12-11 15-7-3-11-8-11-15v-8Z"/><path d="M18 37h12M24 33v4M9 14H5c0 6 3 10 8 10M39 14h4c0 6-3 10-8 10"/></svg>
                </span>
                <h3>{{ $homeContent['why_1_title'] }}</h3>
                <p>{{ $homeContent['why_1_text'] }}</p>
            </article>

            <article class="reason-card" data-reveal>
                <span class="reason-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><path d="M10 9h12c4 0 7 3 7 7v23c-2-3-5-4-9-4H10V9Z"/><path d="M38 9H26M24 14v14M18 21h12"/></svg>
                </span>
                <h3>{{ $homeContent['why_2_title'] }}</h3>
                <p>{{ $homeContent['why_2_text'] }}</p>
            </article>

            <article class="reason-card" data-reveal>
                <span class="reason-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><circle cx="18" cy="18" r="6"/><circle cx="32" cy="19" r="5"/><path d="M7 38c1-8 5-12 11-12s10 4 11 12M27 30c2-3 4-5 7-5 5 0 8 4 9 11"/></svg>
                </span>
                <h3>{{ $homeContent['why_3_title'] }}</h3>
                <p>{{ $homeContent['why_3_text'] }}</p>
            </article>

            <article class="reason-card" data-reveal>
                <span class="reason-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48"><path d="M24 6 39 12v10c0 10-6 17-15 21C15 39 9 32 9 22V12l15-6Z"/><path d="m17 24 5 5 10-11"/></svg>
                </span>
                <h3>{{ $homeContent['why_4_title'] }}</h3>
                <p>{{ $homeContent['why_4_text'] }}</p>
            </article>
        </div>
    </div>
</section>

<section class="home-section home-section--soft">
    <div class="home-shell">
        <div class="section-heading section-heading--compact" data-reveal>
            <div>
                <span class="section-kicker">Programs Offered</span>
                <h2>{{ $homeContent['programs_heading'] }}</h2>
            </div>
            <a class="text-link" href="{{ route('programs') }}">View all programs <span aria-hidden="true">&rarr;</span></a>
        </div>

        <div class="program-grid">
            <article class="program-card program-card--preschool" data-reveal>
                <div class="program-card__visual">
                    <span class="program-card__number">01</span>
                    <span class="program-card__symbol" aria-hidden="true">P</span>
                </div>
                <div class="program-card__body">
                    <span>Early Learning</span>
                    <h3>Preschool</h3>
                    <p>{{ $homeContent['preschool_text'] }}</p>
                    <a href="{{ route('programs') }}">Learn more <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>

            <article class="program-card program-card--elementary" data-reveal>
                <div class="program-card__visual">
                    <span class="program-card__number">02</span>
                    <span class="program-card__symbol" aria-hidden="true">E</span>
                </div>
                <div class="program-card__body">
                    <span>Foundation Years</span>
                    <h3>Elementary</h3>
                    <p>{{ $homeContent['elementary_text'] }}</p>
                    <a href="{{ route('programs') }}">Learn more <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>

            <article class="program-card program-card--junior" data-reveal>
                <div class="program-card__visual">
                    <span class="program-card__number">03</span>
                    <span class="program-card__symbol" aria-hidden="true">JH</span>
                </div>
                <div class="program-card__body">
                    <span>Leadership Years</span>
                    <h3>Junior High School</h3>
                    <p>{{ $homeContent['junior_high_text'] }}</p>
                    <a href="{{ route('programs') }}">Learn more <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="home-section">
    <div class="home-shell home-news-layout">
        <div class="home-news-layout__intro" data-reveal>
            <span class="section-kicker">School Updates</span>
            <h2>{{ $homeContent['updates_heading'] }}</h2>
            <p>{{ $homeContent['updates_intro'] }}</p>
            <div class="news-actions">
                <a class="button button--primary button--small" href="{{ route('announcements.index') }}">View News</a>
                <a class="button button--ghost button--small" href="{{ route('events.index') }}">School Events</a>
            </div>
        </div>

        <div class="announcement-list">
            @forelse($announcements as $announcement)
                <a class="announcement-row" href="{{ route('announcements.show', $announcement) }}" data-reveal>
                    <span class="announcement-row__date">
                        <small>{{ $announcement->published_at?->format('M') }}</small>
                        <strong>{{ $announcement->published_at?->format('d') }}</strong>
                    </span>
                    <span class="announcement-row__content">
                        <small>{{ ucfirst($announcement->type ?? 'Announcement') }}</small>
                        <strong>{{ $announcement->title }}</strong>
                        @if($announcement->excerpt)<span>{{ $announcement->excerpt }}</span>@endif
                    </span>
                    <b aria-hidden="true">&rarr;</b>
                </a>
            @empty
                <div class="empty-state" data-reveal>
                    <span class="empty-state__icon">N</span>
                    <div><strong>No published announcements yet.</strong><p>Approved announcements added through the administrator dashboard will appear here automatically.</p></div>
                </div>
            @endforelse

            @if($events->isNotEmpty())
                <div class="event-mini-grid">
                    @foreach($events as $event)
                        <article class="event-mini-card" data-reveal>
                            <span>{{ $event->starts_at->format('M d') }}</span>
                            <strong>{{ $event->title }}</strong>
                            @if($event->venue)<small>{{ $event->venue }}</small>@endif
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<section class="home-section home-section--life">
    <div class="home-shell">
        <div class="life-heading" data-reveal>
            <div>
                <span class="section-kicker section-kicker--light">Life at NACS-Phil</span>
                <h2>{{ $homeContent['life_heading'] }}</h2>
            </div>
            <a class="button button--light button--small" href="{{ route('gallery.index') }}">View Gallery <span aria-hidden="true">&rarr;</span></a>
        </div>

        @if($galleryItems->isNotEmpty())
            <div class="gallery-preview">
                @foreach($galleryItems->take(5) as $item)
                    <a class="gallery-preview__item gallery-preview__item--{{ $loop->iteration }}" href="{{ route('gallery.index') }}" data-reveal>
                        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->alt_text }}" loading="lazy">
                        <span><strong>{{ $item->title }}</strong><small>{{ $item->category }}</small></span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="gallery-preview gallery-preview--placeholder" data-reveal>
                <div class="gallery-placeholder gallery-placeholder--one"><span>Campus</span></div>
                <div class="gallery-placeholder gallery-placeholder--two"><span>Learning</span></div>
                <div class="gallery-placeholder gallery-placeholder--three"><span>Faith</span></div>
                <div class="gallery-placeholder gallery-placeholder--four"><span>Community</span></div>
                <div class="gallery-placeholder gallery-placeholder--five"><span>Student Life</span></div>
            </div>
            <p class="gallery-note">Approved school photographs will replace these placeholders through the existing gallery administrator.</p>
        @endif
    </div>
</section>

<section class="home-section home-section--cta">
    <div class="home-shell">
        <div class="admission-cta" data-reveal>
            <div class="admission-cta__orb admission-cta__orb--one" aria-hidden="true"></div>
            <div class="admission-cta__orb admission-cta__orb--two" aria-hidden="true"></div>
            <div>
                <span class="section-kicker section-kicker--gold">Begin the conversation</span>
                <h2>{{ $homeContent['cta_heading'] }}</h2>
                <p>{{ $homeContent['cta_text'] }}</p>
            </div>
            <a class="button button--gold" href="{{ route('admissions') }}">{{ $homeContent['cta_button'] }} <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>
@endsection