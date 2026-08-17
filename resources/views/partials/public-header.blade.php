@php
    $nacs11Nav = [
        ['route' => 'home', 'pattern' => 'home', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_home', 'Home')],
        ['route' => 'about', 'pattern' => 'about', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_about', 'About')],
        ['route' => 'programs', 'pattern' => 'programs', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_programs', 'Programs')],
        ['route' => 'admissions', 'pattern' => 'admissions*', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_admissions', 'Admissions')],
        ['route' => 'announcements.index', 'pattern' => 'announcements.*', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_news', 'News')],
        ['route' => 'events.index', 'pattern' => 'events.*', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_events', 'Events')],
        ['route' => 'gallery.index', 'pattern' => 'gallery.*', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_gallery', 'Gallery')],
        ['route' => 'contact', 'pattern' => 'contact', 'label' => \App\Models\SchoolSetting::valueFor('header_nav_contact', 'Contact')],
    ];
    $nacsEmergency = \App\Models\SchoolSetting::valueFor('emergency_banner');
    $nacsHeaderShortName = \App\Models\SchoolSetting::valueFor('header_short_name', \App\Models\SchoolSetting::valueFor('short_name', config('nacs.short_name')));
    $nacsHeaderSchoolName = \App\Models\SchoolSetting::valueFor('header_school_name', \App\Models\SchoolSetting::valueFor('school_name', 'Noel Academy Christian of Sariaya Philippines, Inc.'));
    $nacsResourcesLabel = \App\Models\SchoolSetting::valueFor('header_resources_label', 'Resources');
    $nacsAcademicsLabel = \App\Models\SchoolSetting::valueFor('header_mobile_academics_label', 'Academics');
    $nacsMediaGroupLabel = \App\Models\SchoolSetting::valueFor('header_mobile_media_label', 'News & Media');
    $nacsEnrollLabel = \App\Models\SchoolSetting::valueFor('header_enroll_label', 'Enroll Now');
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
                <strong>{{ $nacsHeaderShortName }}</strong>
                <small>{{ $nacsHeaderSchoolName }}</small>
            </span>
        </a>

        <nav class="nacs11-desktop-nav" aria-label="Primary navigation">
            @foreach($nacs11Nav as $item)
                @php($isActive = request()->routeIs($item['pattern']))
                <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
                    <details class="nacs16-resources {{ request()->routeIs('faculty.*', 'calendar.*', 'documents.*', 'media.*', 'learning-tools.*') ? 'is-active' : '' }}">
                <summary>{{ $nacsResourcesLabel }}</summary>
                <div class="nacs16-resources__menu">
                    <a href="{{ route('faculty.index') }}">Faculty &amp; Staff</a>
                    <a href="{{ route('calendar.index') }}">Academic Calendar</a>
                    <a href="{{ route('documents.index') }}">Documents</a>
                    <a href="{{ route('learning-tools.index') }}">Dictionary &amp; Grammar</a>
                    <a href="{{ route('media.index') }}">Media Hub</a>
                </div>
            </details></nav>

        <div class="nacs11-header__actions">
            <a class="nacs11-button nacs11-button--primary nacs11-header__cta" href="{{ route('admissions.apply') }}">
                {{ $nacsEnrollLabel }} <span aria-hidden="true">&rarr;</span>
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

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/programs,/calendar">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-academics">
                    <span>{{ $nacsAcademicsLabel }}</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
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
                    <span>{{ $nacsMediaGroupLabel }}</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-news" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('announcements.index') }}">News</a>
                    <a href="{{ route('events.index') }}">Events</a>
                    <a href="{{ route('gallery.index') }}">Gallery</a>
                    <a href="{{ route('media.index') }}">Media Hub</a>
                </div>
            </div>

            <div class="nacs45-mobile-group" data-nacs45-mobile-group data-nacs45-prefixes="/documents,/learning-tools">
                <button type="button" class="nacs45-mobile-group__toggle" data-nacs45-mobile-group-toggle aria-expanded="false" aria-controls="nacs45-mobile-resources">
                    <span>{{ $nacsResourcesLabel }}</span><span class="nacs45-mobile-group__chevron" aria-hidden="true"></span>
                </button>
                <div id="nacs45-mobile-resources" class="nacs45-mobile-group__panel" data-nacs45-mobile-group-panel hidden>
                    <a href="{{ route('documents.index') }}">School Documents</a>
                    <a href="{{ route('learning-tools.index') }}">Dictionary &amp; Grammar</a>
                </div>
            </div>

            <a href="{{ route('contact') }}" class="nacs45-mobile-direct">{{ \App\Models\SchoolSetting::valueFor('header_nav_contact', 'Contact') }}</a>

            <a class="nacs11-button nacs11-button--primary nacs45-mobile-enroll" href="{{ route('admissions.apply') }}">{{ $nacsEnrollLabel }} <span aria-hidden="true">&rarr;</span></a>
        </div>
    </nav>
</header>
