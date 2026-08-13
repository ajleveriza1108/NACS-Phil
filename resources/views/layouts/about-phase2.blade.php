@php($aboutContent = \App\Models\SiteContent::valuesFor('about', \App\Support\AboutContent::defaults()))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061d3b">
    <title>About | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="Learn about {{ config('nacs.short_name') }}, its school story, mission, vision, Christian foundation, values, and leadership.">
    <link rel="stylesheet" href="{{ asset('assets/phase2-about/about.css') }}">
    <script src="{{ asset('assets/phase2-about/about.js') }}" defer></script>
</head>
<body class="about-phase2">
<a href="#about-main" class="about-skip-link">Skip to content</a>

<div class="about-dev-strip">
    <div class="about-shell about-dev-strip__inner">
        <span aria-hidden="true"></span>
        <p>Development preview â€” official history, mission, vision, values, and leadership wording require school approval before public launch.</p>
    </div>
</div>

<header class="about-header" data-about-header>
    <div class="about-shell about-header__inner">
        <a href="{{ route('home') }}" class="about-brand" aria-label="{{ config('nacs.short_name') }} home">
            <span class="about-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
            <span class="about-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
        </a>

        <nav class="about-desktop-nav" aria-label="Primary navigation">
            <a href="{{ route('home') }}">Home</a>
            <a class="is-active" href="{{ route('about') }}">About</a>
            <a href="{{ route('programs') }}">Programs</a>
            <a href="{{ route('admissions') }}">Admissions</a>
            <a href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>

        <div class="about-header__actions">
            <a class="about-button about-button--primary about-enroll" href="{{ route('admissions') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
            <button type="button" class="about-menu-button" data-about-menu-button aria-expanded="false" aria-controls="about-mobile-nav">
                <span class="about-sr-only">Open navigation</span><i></i><i></i><i></i>
            </button>
        </div>
    </div>

    <nav id="about-mobile-nav" class="about-mobile-nav" data-about-mobile-nav hidden aria-label="Mobile navigation">
        <div class="about-shell about-mobile-nav__inner">
            <a href="{{ route('home') }}">Home</a>
            <a class="is-active" href="{{ route('about') }}">About</a>
            <a href="{{ route('programs') }}">Programs</a>
            <a href="{{ route('admissions') }}">Admissions</a>
            <a href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a class="about-button about-button--primary" href="{{ route('admissions') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
        </div>
    </nav>
</header>

<main id="about-main">@yield('content')</main>

<footer class="about-footer">
    <div class="about-shell about-footer__grid">
        <section class="about-footer__brand">
            <a href="{{ route('home') }}" class="about-brand about-brand--footer">
                <span class="about-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
                <span class="about-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
            </a>
            <p>Faith. Character. Excellence.</p>
        </section>
        <section><h2>Explore</h2><div class="about-footer__links"><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('gallery.index') }}">Gallery</a></div></section>
        <section><h2>School</h2><div class="about-footer__links"><a href="{{ route('about') }}">About</a><a href="{{ route('contact') }}">Contact</a><a href="{{ route('privacy') }}">Privacy</a></div></section>
        <section><h2>Location</h2><p>{{ config('nacs.address') }}</p><a class="about-footer__inline" href="{{ route('contact') }}">Contact the school &rarr;</a></section>
    </div>
    <div class="about-shell about-footer__bottom"><span>&copy; {{ now()->year }} {{ config('nacs.short_name') }}. All rights reserved.</span><span>Official content must be approved before public launch.</span></div>
</footer>
</body>
</html>