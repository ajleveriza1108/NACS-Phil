@php
    $nacs11Nav = [
        ['route' => 'home', 'pattern' => 'home', 'label' => 'Home'],
        ['route' => 'about', 'pattern' => 'about', 'label' => 'About'],
        ['route' => 'programs', 'pattern' => 'programs', 'label' => 'Programs'],
        ['route' => 'admissions', 'pattern' => 'admissions*', 'label' => 'Admissions'],
        ['route' => 'announcements.index', 'pattern' => 'announcements.*', 'label' => 'News'],
        ['route' => 'events.index', 'pattern' => 'events.*', 'label' => 'Events'],
        ['route' => 'gallery.index', 'pattern' => 'gallery.*', 'label' => 'Gallery'],
        ['route' => 'contact', 'pattern' => 'contact', 'label' => 'Contact'],
    ];
    $nacsEmergency = \App\Models\SchoolSetting::valueFor('emergency_banner');
@endphp

<a class="nacs11-skip" href="#{{ $mainId ?? 'main-content' }}">Skip to content</a>

@if($nacsEmergency)
<div class="nacs12-emergency" role="status">
    <div class="nacs11-shell"><strong>Important:</strong> {{ $nacsEmergency }}</div>
</div>
@endif

@if(!app()->environment('production'))
<div class="nacs11-preview">
    <div class="nacs11-shell nacs11-preview__inner">
        <span class="nacs11-preview__dot" aria-hidden="true"></span>
        <span>Development preview &mdash; official school content and photographs remain subject to authorized school approval before public launch.</span>
    </div>
</div>
@endif

<header class="nacs11-header" data-nacs11-header>
    <div class="nacs11-shell nacs11-header__inner">
        <a href="{{ route('home') }}" class="nacs11-brand" aria-label="{{ config('nacs.short_name') }} home">
            <span class="nacs11-brand__mark">
                <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="46" height="46">
            </span>
            <span class="nacs11-brand__copy">
                <strong>{{ \App\Models\SchoolSetting::valueFor('short_name', config('nacs.short_name')) }}</strong>
                <small>Noel Academy Christian of Sariaya Philippines, Inc.</small>
            </span>
        </a>

        <nav class="nacs11-desktop-nav" aria-label="Primary navigation">
            @foreach($nacs11Nav as $item)
                @php($isActive = request()->routeIs($item['pattern']))
                <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
                    <details class="nacs16-resources {{ request()->routeIs('faculty.*', 'calendar.*', 'documents.*', 'media.*') ? 'is-active' : '' }}">
                <summary>Resources</summary>
                <div class="nacs16-resources__menu">
                    <a href="{{ route('faculty.index') }}">Faculty &amp; Staff</a>
                    <a href="{{ route('calendar.index') }}">Academic Calendar</a>
                    <a href="{{ route('documents.index') }}">Documents</a>
                    <a href="{{ route('media.index') }}">Media Hub</a>
                </div>
            </details></nav>

        <div class="nacs11-header__actions">
            <a class="nacs11-button nacs11-button--primary nacs11-header__cta" href="{{ route('admissions') }}">
                Enroll Now <span aria-hidden="true">&rarr;</span>
            </a>
            <button class="nacs11-menu-button" type="button" data-nacs11-menu-button aria-expanded="false" aria-controls="nacs11-mobile-nav">
                <span class="nacs11-sr-only">Open navigation</span>
                <i></i><i></i><i></i>
            </button>
        </div>
    </div>

    <nav id="nacs11-mobile-nav" class="nacs11-mobile-nav" data-nacs11-mobile-nav hidden aria-label="Mobile navigation">
        <div class="nacs11-shell nacs11-mobile-nav__inner nacs45-mobile-nav">
            <a href="{{ route('home') }}" class="nacs45-mobile-direct">Home</a>

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/about,/faculty">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-about">
                    <span>About</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-about" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('about') }}">About NACS-Phil</a>
                    <a href="{{ route('faculty.index') }}">Faculty &amp; Staff</a>
                </div>
            </div>

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/programs,/academic-calendar">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-academics">
                    <span>Academics</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-academics" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('programs') }}">Programs</a>
                    <a href="{{ route('calendar.index') }}">Academic Calendar</a>
                </div>
            </div>

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/admissions">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-admissions">
                    <span>Admissions</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-admissions" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('admissions') }}">Admissions Information</a>
                    <a href="{{ route('admissions.apply') }}">Start Application</a>
                    <a href="{{ route('admissions.track') }}">Track Application</a>
                </div>
            </div>

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/announcements,/events,/gallery,/media">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-news">
                    <span>News &amp; Media</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-news" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('announcements.index') }}">News</a>
                    <a href="{{ route('events.index') }}">Events</a>
                    <a href="{{ route('gallery.index') }}">Gallery</a>
                    <a href="{{ route('media.index') }}">Media Hub</a>
                </div>
            </div>

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/documents">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-resources">
                    <span>Resources</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-resources" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('documents.index') }}">School Documents</a>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="nacs45-mobile-direct">Contact</a>

            <a class="nacs11-button nacs11-button--primary nacs45-mobile-enroll" href="{{ route('admissions.apply') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
        </div>
    </nav>
</header>
