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
        <div class="nacs11-shell nacs11-mobile-nav__inner">
            @foreach($nacs11Nav as $item)
                @php($isActive = request()->routeIs($item['pattern']))
                <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('faculty.index') }}">Faculty &amp; Staff</a>
            <a href="{{ route('calendar.index') }}">Academic Calendar</a>
            <a href="{{ route('documents.index') }}">Documents</a>
            <a href="{{ route('media.index') }}">Media Hub</a>
            <a class="nacs11-button nacs11-button--primary" href="{{ route('admissions') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
        </div>
    </nav>
</header>
