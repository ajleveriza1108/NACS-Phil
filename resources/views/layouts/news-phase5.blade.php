<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061d3b">
    <title>@yield('title', 'News') | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="@yield('meta_description', 'School news, announcements, notices, and community updates from NACS-Phil.')">
    <link rel="stylesheet" href="{{ asset('assets/phase5-news/news.css') }}">
    <script src="{{ asset('assets/phase5-news/news.js') }}" defer></script>
</head>
<body class="news-phase5">
<a href="#news-main" class="news-skip">Skip to content</a>

<div class="news-dev-strip">
    <div class="news-shell news-dev-strip__inner">
        <span aria-hidden="true"></span>
        <p>Development preview â€” official announcements should be published only by authorized school staff.</p>
    </div>
</div>

<header class="news-header" data-news-header>
    <div class="news-shell news-header__inner">
        <a href="{{ route('home') }}" class="news-brand" aria-label="{{ config('nacs.short_name') }} home">
            <span class="news-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
            <span class="news-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
        </a>

        <nav class="news-desktop-nav" aria-label="Primary navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('programs') }}">Programs</a>
            <a href="{{ route('admissions') }}">Admissions</a>
            <a class="is-active" href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>

        <div class="news-header__actions">
            <a class="news-button news-button--primary news-contact" href="{{ route('contact') }}">Contact <span aria-hidden="true">&rarr;</span></a>
            <button type="button" class="news-menu-button" data-news-menu-button aria-expanded="false" aria-controls="news-mobile-nav">
                <span class="news-sr-only">Open navigation</span><i></i><i></i><i></i>
            </button>
        </div>
    </div>

    <nav id="news-mobile-nav" class="news-mobile-nav" data-news-mobile-nav hidden aria-label="Mobile navigation">
        <div class="news-shell news-mobile-nav__inner">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('programs') }}">Programs</a>
            <a href="{{ route('admissions') }}">Admissions</a>
            <a class="is-active" href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
        </div>
    </nav>
</header>

<main id="news-main">@yield('content')</main>

<footer class="news-footer">
    <div class="news-shell news-footer__grid">
        <section class="news-footer__brand">
            <a href="{{ route('home') }}" class="news-brand news-brand--footer">
                <span class="news-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
                <span class="news-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
            </a>
            <p>Faith. Character. Excellence.</p>
        </section>
        <section><h2>Updates</h2><div class="news-footer__links"><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a></div></section>
        <section><h2>Explore</h2><div class="news-footer__links"><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('privacy') }}">Privacy</a></div></section>
        <section><h2>Location</h2><p>{{ config('nacs.address') }}</p><a class="news-footer__inline" href="{{ route('contact') }}">Contact the school &rarr;</a></section>
    </div>
    <div class="news-shell news-footer__bottom"><span>&copy; {{ now()->year }} {{ config('nacs.short_name') }}. All rights reserved.</span><span>Published school updates.</span></div>
</footer>
</body>
</html>