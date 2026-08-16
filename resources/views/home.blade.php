@extends('layouts.site-current', ['bodyClass' => 'nacs-home-phase1 nacs-current-page nacs-current-page--home', 'mainId' => 'main-content', 'mainClass' => '', 'assetBundle' => 'home', 'useVite' => false, 'title' => 'Home', 'description' => 'Discover NACS-Phil programs, Christian education, school announcements, events, admissions information, and campus life.'])
@section('content')
<section class="hero p18-hero">
    <div class="hero__mesh" aria-hidden="true"></div>
    <div class="hero__glow hero__glow--one" aria-hidden="true"></div>
    <div class="hero__glow hero__glow--two" aria-hidden="true"></div>

    <div class="home-shell hero__grid p18-hero__grid">
        <div class="hero__content" data-reveal>
            <span class="eyebrow-pill p18-eyebrow">
                <span class="p18-eyebrow__line" aria-hidden="true"></span>
                <span data-visual-field="hero_badge">{{ $homeContent['hero_badge'] }}</span>
            </span>

            <h1><span data-visual-field="hero_heading">{{ $homeContent['hero_heading'] }}</span> <span><span data-visual-field="hero_highlight">{{ $homeContent['hero_highlight'] }}</span></span></h1>

            <p class="hero__lead"><span data-visual-field="hero_lead">{{ $homeContent['hero_lead'] }}</span></p>

            <div class="hero__actions p18-hero__actions">
                <a class="button button--primary" href="{{ route('programs') }}">
                    <span data-visual-field="hero_primary_button">{{ $homeContent['hero_primary_button'] }}</span> <span aria-hidden="true">&rarr;</span>
                </a>
                <a class="button button--secondary" href="{{ route('admissions.apply') }}">
                    <span data-visual-field="hero_secondary_button">{{ $homeContent['hero_secondary_button'] }}</span> <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>

        <div class="hero__visual p18-hero__visual" data-reveal>
            <div class="hero__visual-frame p18-hero__visual-frame" style="overflow:hidden">
                @if(!empty($homeContent['hero_image_path']))
                    <img
                        src="{{ Storage::url($homeContent['hero_image_path']) }}"
                        alt="{{ $homeContent['hero_image_alt'] }}"
                        data-visual-image="hero_image"
                        style="object-fit:cover;object-position:{{ (float) ($homeContent['hero_image_focus_x'] ?? 50) }}% {{ (float) ($homeContent['hero_image_focus_y'] ?? 50) }}%;transform:scale({{ (float) ($homeContent['hero_image_zoom'] ?? 1) }});transform-origin:center"
                    >
                @elseif($galleryItems->isNotEmpty())
                    <img src="{{ Storage::url($galleryItems->first()->image_path) }}" alt="{{ $galleryItems->first()->alt_text }}">
                @else
                    <div class="p18-hero__fallback" role="img" aria-label="{{ $homeContent['hero_image_alt'] }}">
                        <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="">
                        <span>Official school photography can be added through the existing content and gallery tools.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="home-shell p18-quick-links" data-reveal>
        <a href="{{ route('admissions') }}" class="p18-quick-card">
            <span class="p18-quick-card__icon" aria-hidden="true"><svg class="p43-quick-icon" viewBox="0 0 24 24" focusable="false"><path d="M4 10.5 12 4l8 6.5"/><path d="M6.5 9.5V20h11V9.5"/><path d="M9.5 20v-6h5v6"/></svg></span>
            <span><strong>Admissions</strong><small>Steps to enroll</small></span>
            <b aria-hidden="true">&rsaquo;</b>
        </a>

        <a href="{{ route('calendar.index') }}" class="p18-quick-card">
            <span class="p18-quick-card__icon" aria-hidden="true"><svg class="p43-quick-icon" viewBox="0 0 24 24" focusable="false"><rect x="4" y="5.5" width="16" height="14" rx="2"/><path d="M8 3.5v4M16 3.5v4M4 9.5h16M8 13h2M12 13h2M16 13h1M8 16.5h2M12 16.5h2M16 16.5h1"/></svg></span>
            <span><strong>Academic Calendar</strong><small>Key dates &amp; schedules</small></span>
            <b aria-hidden="true">&rsaquo;</b>
        </a>

        <a href="{{ route('faculty.index') }}" class="p18-quick-card">
            <span class="p18-quick-card__icon" aria-hidden="true"><svg class="p43-quick-icon" viewBox="0 0 24 24" focusable="false"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3.5 19c.7-3.6 2.6-5.5 5.5-5.5s4.8 1.9 5.5 5.5M14.5 14.2c2.8-.5 5.2 1.1 6 4.3"/></svg></span>
            <span><strong>Faculty &amp; Staff</strong><small>Our dedicated educators</small></span>
            <b aria-hidden="true">&rsaquo;</b>
        </a>

        <a href="{{ route('documents.index') }}" class="p18-quick-card">
            <span class="p18-quick-card__icon" aria-hidden="true"><svg class="p43-quick-icon" viewBox="0 0 24 24" focusable="false"><path d="M6 3.5h8l4 4V20.5H6z"/><path d="M14 3.5v4h4M9 12h6M9 15.5h6"/></svg></span>
            <span><strong>School Documents</strong><small>Forms &amp; policies</small></span>
            <b aria-hidden="true">&rsaquo;</b>
        </a>

        <a href="{{ route('contact') }}" class="p18-quick-card">
            <span class="p18-quick-card__icon" aria-hidden="true"><svg class="p43-quick-icon" viewBox="0 0 24 24" focusable="false"><path d="M4 5.5h16v11H9l-5 4z"/><path d="M8 10h8M8 13h5"/></svg></span>
            <span><strong>Parent Inquiry</strong><small>We're here to help</small></span>
            <b aria-hidden="true">&rsaquo;</b>
        </a>
    </div>
</section>

@if($featuredAnnouncement)
<section class="home-shell feature-notice p18-feature-notice" data-reveal>
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

<section class="home-section p18-about">
    <div class="home-shell">
        <div class="p18-about__grid">
            <article class="p18-about__copy" data-reveal>
                <span class="section-kicker">About NACS-Phil</span>
                <h2><span data-visual-field="why_heading">{{ $homeContent['why_heading'] }}</span></h2>
                <p><span data-visual-field="why_intro">{{ $homeContent['why_intro'] }}</span></p>

                <ul class="p18-about__points">
                    <li><span aria-hidden="true">+</span><strong><span data-visual-field="why_2_title">{{ $homeContent['why_2_title'] }}</span></strong></li>
                    <li><span aria-hidden="true">+</span><strong><span data-visual-field="why_3_title">{{ $homeContent['why_3_title'] }}</span></strong></li>
                    <li><span aria-hidden="true">+</span><strong><span data-visual-field="why_4_title">{{ $homeContent['why_4_title'] }}</span></strong></li>
                </ul>

                <a class="button button--secondary button--small" href="{{ route('about') }}">
                    Learn Our Story <span aria-hidden="true">&rarr;</span>
                </a>
            </article>

            <div class="p18-about__media" data-reveal>
                @if($galleryItems->isNotEmpty())
                    <img src="{{ Storage::url($galleryItems->first()->image_path) }}" alt="{{ $galleryItems->first()->alt_text }}" loading="lazy">
                @else
                    <div class="p18-about__media-fallback">
                        <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="">
                        <span>Approved school photography will appear here when available.</span>
                    </div>
                @endif
            </div>

            <article class="p18-foundation-card" data-reveal>
                <span class="p18-foundation-card__quote" aria-hidden="true">&ldquo;</span>
                <span class="section-kicker">Our Foundation</span>
                <h3><span data-visual-field="why_1_title">{{ $homeContent['why_1_title'] }}</span></h3>
                <p><span data-visual-field="why_1_text">{{ $homeContent['why_1_text'] }}</span></p>
                <span class="p18-foundation-card__cross" aria-hidden="true">+</span>
            </article>
        </div>
    </div>
</section>

<section class="home-section home-section--soft p18-programs">
    <div class="home-shell">
        <div class="section-heading section-heading--compact" data-reveal>
            <div>
                <span class="section-kicker">Programs for Every Stage</span>
                <h2><span data-visual-field="programs_heading">{{ $homeContent['programs_heading'] }}</span></h2>
            </div>
            <a class="text-link" href="{{ route('programs') }}">View all programs <span aria-hidden="true">&rarr;</span></a>
        </div>

        <div class="p18-program-grid">
            <article class="p18-program-card" data-reveal>
                <div class="p18-program-card__visual p18-program-card__visual--preschool">
                    <span aria-hidden="true">P</span>
                </div>
                <div>
                    <small>Early Learning</small>
                    <h3>Preschool</h3>
                    <p><span data-visual-field="preschool_text">{{ $homeContent['preschool_text'] }}</span></p>
                    <a href="{{ route('programs') }}">Learn More <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>

            <article class="p18-program-card" data-reveal>
                <div class="p18-program-card__visual p18-program-card__visual--elementary">
                    <span aria-hidden="true">E</span>
                </div>
                <div>
                    <small>Grades 1-6</small>
                    <h3>Elementary</h3>
                    <p><span data-visual-field="elementary_text">{{ $homeContent['elementary_text'] }}</span></p>
                    <a href="{{ route('programs') }}">Learn More <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>

            <article class="p18-program-card" data-reveal>
                <div class="p18-program-card__visual p18-program-card__visual--junior">
                    <span aria-hidden="true">JH</span>
                </div>
                <div>
                    <small>Grades 7-10</small>
                    <h3>Junior High School</h3>
                    <p><span data-visual-field="junior_high_text">{{ $homeContent['junior_high_text'] }}</span></p>
                    <a href="{{ route('programs') }}">Learn More <span aria-hidden="true">&rarr;</span></a>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="p18-values-band">
    <div class="home-shell p18-values-band__grid">
        <div data-reveal><span aria-hidden="true">+</span><strong>Biblical Foundation</strong><small>Faith-centered learning</small></div>
        <div data-reveal><span aria-hidden="true">&#9733;</span><strong>Academic Excellence</strong><small>Purposeful growth</small></div>
        <div data-reveal><span aria-hidden="true">&#9675;</span><strong>Character Formation</strong><small>Values in action</small></div>
        <div data-reveal><span aria-hidden="true">&#9825;</span><strong>Community</strong><small>Learning together</small></div>
    </div>
</section>

<section class="home-section p18-updates">
    <div class="home-shell">
        <div class="p18-updates__grid">
            <section class="p18-news-panel" data-reveal>
                <div class="p18-panel-heading">
                    <div>
                        <span class="section-kicker">Latest News</span>
                        <h2><span data-visual-field="updates_heading">{{ $homeContent['updates_heading'] }}</span></h2>
                    </div>
                    <a href="{{ route('announcements.index') }}">View all news <span aria-hidden="true">&rarr;</span></a>
                </div>

                <div class="p18-news-list">
                    @forelse($announcements->take(3) as $announcement)
                        <a class="p18-news-card" href="{{ route('announcements.show', $announcement) }}">
                            <span class="p18-news-card__date">
                                <small>{{ $announcement->published_at?->format('M') }}</small>
                                <strong>{{ $announcement->published_at?->format('d') }}</strong>
                            </span>
                            <span>
                                <small>{{ ucfirst($announcement->type ?? 'Announcement') }}</small>
                                <strong>{{ $announcement->title }}</strong>
                                @if($announcement->excerpt)<p>{{ $announcement->excerpt }}</p>@endif
                            </span>
                        </a>
                    @empty
                        <div class="p18-empty">
                            <strong>No published news yet.</strong>
                            <p>Approved announcements will appear here automatically.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="p18-events-panel" data-reveal>
                <div class="p18-panel-heading">
                    <div>
                        <span class="section-kicker">Upcoming Events</span>
                        <h2>School Calendar</h2>
                    </div>
                    <a href="{{ route('events.index') }}">View all events <span aria-hidden="true">&rarr;</span></a>
                </div>

                <div class="p18-event-list">
                    @forelse($events->take(4) as $event)
                        <article class="p18-event-row">
                            <span class="p18-event-row__date">
                                <small>{{ $event->starts_at->format('M') }}</small>
                                <strong>{{ $event->starts_at->format('d') }}</strong>
                            </span>
                            <span>
                                <strong>{{ $event->title }}</strong>
                                <small>{{ $event->starts_at->format('g:i A') }}@if($event->venue) &middot; {{ $event->venue }}@endif</small>
                            </span>
                        </article>
                    @empty
                        <div class="p18-empty">
                            <strong>No public events yet.</strong>
                            <p>Published school events will appear here automatically.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</section>

<section class="home-section home-section--life p18-life">
    <div class="home-shell">
        <div class="life-heading" data-reveal>
            <div>
                <span class="section-kicker section-kicker--light">Life at NACS-Phil</span>
                <h2><span data-visual-field="life_heading">{{ $homeContent['life_heading'] }}</span></h2>
            </div>
            <a class="button button--light button--small" href="{{ route('media.index') }}">Explore Media <span aria-hidden="true">&rarr;</span></a>
        </div>

        @if($galleryItems->isNotEmpty())
            <div class="gallery-preview p18-gallery-preview">
                @foreach($galleryItems->take(5) as $item)
                    <a class="gallery-preview__item gallery-preview__item--{{ $loop->iteration }}" href="{{ route('gallery.index') }}" data-reveal>
                        <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->alt_text }}" loading="lazy">
                        <span><strong>{{ $item->title }}</strong><small>{{ $item->category }}</small></span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="gallery-preview gallery-preview--placeholder p18-gallery-preview" data-reveal>
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

<section class="home-section home-section--cta p18-cta">
    <div class="home-shell">
        <div class="admission-cta" data-reveal>
            <div class="admission-cta__orb admission-cta__orb--one" aria-hidden="true"></div>
            <div class="admission-cta__orb admission-cta__orb--two" aria-hidden="true"></div>
            <div>
                <span class="section-kicker section-kicker--gold">Begin the conversation</span>
                <h2><span data-visual-field="cta_heading">{{ $homeContent['cta_heading'] }}</span></h2>
                <p><span data-visual-field="cta_text">{{ $homeContent['cta_text'] }}</span></p>
            </div>
            <a class="button button--gold" href="{{ route('admissions') }}"><span data-visual-field="cta_button">{{ $homeContent['cta_button'] }}</span> <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>
@endsection
