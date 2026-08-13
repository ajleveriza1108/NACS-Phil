<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061d3b">
    <title>Admissions | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="Admissions information, steps, requirements, FAQs, and family guidance for {{ config('nacs.short_name') }}.">
    <link rel="stylesheet" href="{{ asset('assets/phase4-admissions/admissions.css') }}">
    <script src="{{ asset('assets/phase4-admissions/admissions.js') }}" defer></script>
</head>
<body class="admissions-phase4">
<a href="#admissions-main" class="admissions-skip">Skip to content</a>

<div class="admissions-dev-strip">
    <div class="admissions-shell admissions-dev-strip__inner">
        <span aria-hidden="true"></span>
        <p>Development preview â€” admissions requirements, dates, availability, and policies require school approval before public launch.</p>
    </div>
</div>

<header class="admissions-header" data-admissions-header>
    <div class="admissions-shell admissions-header__inner">
        <a href="{{ route('home') }}" class="admissions-brand" aria-label="{{ config('nacs.short_name') }} home">
            <span class="admissions-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
            <span class="admissions-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
        </a>

        <nav class="admissions-desktop-nav" aria-label="Primary navigation">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('programs') }}">Programs</a>
            <a class="is-active" href="{{ route('admissions') }}">Admissions</a>
            <a href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
        </nav>

        <div class="admissions-header__actions">
            <a class="admissions-button admissions-button--primary admissions-contact" href="{{ route('contact') }}">Ask Admissions <span aria-hidden="true">&rarr;</span></a>
            <button type="button" class="admissions-menu-button" data-admissions-menu-button aria-expanded="false" aria-controls="admissions-mobile-nav">
                <span class="admissions-sr-only">Open navigation</span><i></i><i></i><i></i>
            </button>
        </div>
    </div>

    <nav id="admissions-mobile-nav" class="admissions-mobile-nav" data-admissions-mobile-nav hidden aria-label="Mobile navigation">
        <div class="admissions-shell admissions-mobile-nav__inner">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('programs') }}">Programs</a>
            <a class="is-active" href="{{ route('admissions') }}">Admissions</a>
            <a href="{{ route('announcements.index') }}">News</a>
            <a href="{{ route('events.index') }}">Events</a>
            <a href="{{ route('gallery.index') }}">Gallery</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a class="admissions-button admissions-button--primary" href="{{ route('contact') }}">Ask Admissions <span aria-hidden="true">&rarr;</span></a>
        </div>
    </nav>
</header>

<main id="admissions-main">@yield('content')</main>

<footer class="admissions-footer">
    <div class="admissions-shell admissions-footer__grid">
        <section class="admissions-footer__brand">
            <a href="{{ route('home') }}" class="admissions-brand admissions-brand--footer">
                <span class="admissions-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
                <span class="admissions-brand__copy"><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
            </a>
            <p>Faith. Character. Excellence.</p>
        </section>
        <section><h2>Admissions</h2><div class="admissions-footer__links"><a href="#admission-steps">Steps</a><a href="#requirements">Requirements</a><a href="#admissions-faq">FAQ</a></div></section>
        <section><h2>Explore</h2><div class="admissions-footer__links"><a href="{{ route('programs') }}">Programs</a><a href="{{ route('about') }}">About</a><a href="{{ route('contact') }}">Contact</a><a href="{{ route('privacy') }}">Privacy</a></div></section>
        <section><h2>Location</h2><p>{{ config('nacs.address') }}</p><a class="admissions-footer__inline" href="{{ route('contact') }}">Contact the school &rarr;</a></section>
    </div>
    <div class="admissions-shell admissions-footer__bottom"><span>&copy; {{ now()->year }} {{ config('nacs.short_name') }}. All rights reserved.</span><span>Admissions information is subject to school confirmation.</span></div>
</footer>
</body>
</html>