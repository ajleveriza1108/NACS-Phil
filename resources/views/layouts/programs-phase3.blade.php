<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061d3b">
    <title>Programs | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="Explore Preschool, Elementary, and Junior High programs at {{ config('nacs.short_name') }}.">
    <link rel="stylesheet" href="{{ asset('assets/phase3-programs/programs.css') }}">
    <script src="{{ asset('assets/phase3-programs/programs.js') }}" defer></script>
</head>
<body class="programs-phase3">
<a href="#programs-main" class="programs-skip">Skip to content</a>

<div class="programs-dev-strip">
    <div class="programs-shell programs-dev-strip__inner">
        <span aria-hidden="true"></span>
        <p>Development preview â€” program descriptions and current offerings should be confirmed by authorized school leadership before public launch.</p>
    </div>
</div>

<header class="programs-header" data-programs-header>
    <div class="programs-shell programs-header__inner">
        <a href="{{ route('home') }}" class="programs-brand" aria-label="{{ config('nacs.short_name') }} home">
            <span class="programs-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
            <span class="programs-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
        </a>

        <nav class="programs-desktop-nav" aria-label="Primary navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a class="is-active" href="{{ route('programs') }}">Programs</a>
            <a href="{{ route('admissions') }}">Admissions</a>
            <a href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>

        <div class="programs-header__actions">
            <a class="programs-button programs-button--primary programs-enroll" href="{{ route('admissions') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
            <button type="button" class="programs-menu-button" data-programs-menu-button aria-expanded="false" aria-controls="programs-mobile-nav">
                <span class="programs-sr-only">Open navigation</span><i></i><i></i><i></i>
            </button>
        </div>
    </div>

    <nav id="programs-mobile-nav" class="programs-mobile-nav" data-programs-mobile-nav hidden aria-label="Mobile navigation">
        <div class="programs-shell programs-mobile-nav__inner">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a class="is-active" href="{{ route('programs') }}">Programs</a>
            <a href="{{ route('admissions') }}">Admissions</a>
            <a href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a class="programs-button programs-button--primary" href="{{ route('admissions') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
        </div>
    </nav>
</header>

<main id="programs-main">@yield('content')</main>

<footer class="programs-footer">
    <div class="programs-shell programs-footer__grid">
        <section class="programs-footer__brand">
            <a href="{{ route('home') }}" class="programs-brand programs-brand--footer">
                <span class="programs-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
                <span class="programs-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
            </a>
            <p>Faith. Character. Excellence.</p>
        </section>
        <section><h2>Programs</h2><div class="programs-footer__links"><a href="#preschool">Preschool</a><a href="#elementary">Elementary</a><a href="#junior-high">Junior High</a></div></section>
        <section><h2>Explore</h2><div class="programs-footer__links"><a href="{{ route('about') }}">About</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('contact') }}">Contact</a><a href="{{ route('privacy') }}">Privacy</a></div></section>
        <section><h2>Location</h2><p>{{ config('nacs.address') }}</p><a class="programs-footer__inline" href="{{ route('contact') }}">Contact the school &rarr;</a></section>
    </div>
    <div class="programs-shell programs-footer__bottom"><span>&copy; {{ now()->year }} {{ config('nacs.short_name') }}. All rights reserved.</span><span>Program information is subject to school confirmation.</span></div>
</footer>
</body>
</html>