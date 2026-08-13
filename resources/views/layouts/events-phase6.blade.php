<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061d3b">
    <title>@yield('title','Events') | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="@yield('meta_description','Upcoming school events and activities at NACS-Phil.')">
    <link rel="stylesheet" href="{{ asset('assets/phase6-events/events.css') }}">
    <script src="{{ asset('assets/phase6-events/events.js') }}" defer></script>
</head>
<body class="events-phase6">
<a href="#events-main" class="events-skip">Skip to content</a>
<div class="events-dev"><div class="events-shell"><span></span>Development preview â€” event details should be confirmed by authorized school staff.</div></div>

<header class="events-header" data-events-header>
<div class="events-shell events-header__inner">
    <a href="{{ route('home') }}" class="events-brand">
        <span class="events-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
        <span><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
    </a>
    <nav class="events-desktop-nav" aria-label="Primary navigation">
        <a href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a class="is-active" href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a><a href="{{ route('contact') }}">Contact</a>
    </nav>
    <button class="events-menu-button" type="button" data-events-menu-button aria-expanded="false" aria-controls="events-mobile-nav"><span></span><span></span><span></span><b>Menu</b></button>
</div>
<nav id="events-mobile-nav" class="events-mobile-nav" data-events-mobile-nav hidden>
    <div class="events-shell"><a href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a class="is-active" href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a><a href="{{ route('contact') }}">Contact</a></div>
</nav>
</header>

<main id="events-main">@yield('content')</main>

<footer class="events-footer">
<div class="events-shell events-footer__grid">
    <div><a href="{{ route('home') }}" class="events-brand events-brand--footer"><span class="events-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt=""></span><span><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span></a><p>Faith. Character. Excellence.</p></div>
    <div><h2>Updates</h2><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a></div>
    <div><h2>Explore</h2><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a></div>
    <div><h2>Contact</h2><p>{{ config('nacs.address') }}</p><a href="{{ route('contact') }}">Contact the school &rarr;</a></div>
</div>
<div class="events-shell events-footer__bottom">&copy; {{ now()->year }} {{ config('nacs.short_name') }}</div>
</footer>
</body>
</html>