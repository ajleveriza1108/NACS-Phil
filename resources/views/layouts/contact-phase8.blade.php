<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#061d3b">
    <title>@yield('title','Contact') | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="Contact the NACS-Phil school office or submit a general school inquiry.">
    <link rel="stylesheet" href="{{ asset('assets/phase8-contact/contact.css') }}">
    <script src="{{ asset('assets/phase8-contact/contact.js') }}" defer></script>
</head>
<body class="contact-phase8">
<a href="#contact-main" class="contact-skip">Skip to content</a>
<div class="contact-dev"><div class="contact-shell"><span></span>Development preview â€” verify official school contact information before public launch.</div></div>

<header class="contact-header" data-contact-header>
<div class="contact-shell contact-header__inner">
    <a href="{{ route('home') }}" class="contact-brand">
        <span class="contact-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52"></span>
        <span><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span>
    </a>
    <nav class="contact-desktop-nav" aria-label="Primary navigation">
        <a href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a><a class="is-active" href="{{ route('contact') }}">Contact</a>
    </nav>
    <button class="contact-menu-button" type="button" data-contact-menu-button aria-expanded="false" aria-controls="contact-mobile-nav"><span></span><span></span><span></span><b>Menu</b></button>
</div>
<nav id="contact-mobile-nav" class="contact-mobile-nav" data-contact-mobile-nav hidden aria-label="Mobile navigation">
<div class="contact-shell"><a href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a><a class="is-active" href="{{ route('contact') }}">Contact</a></div>
</nav>
</header>

<main id="contact-main">@yield('content')</main>

<footer class="contact-footer">
<div class="contact-shell contact-footer__grid">
<div><a href="{{ route('home') }}" class="contact-brand contact-brand--footer"><span class="contact-brand__mark"><img src="{{ asset('images/nacs-development-mark.svg') }}" alt=""></span><span><strong>{{ config('nacs.short_name') }}</strong><small>Noel Academy Christian of Sariaya Philippines</small></span></a><p>Faith. Character. Excellence.</p></div>
<div><h2>Updates</h2><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a></div>
<div><h2>Explore</h2><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a></div>
<div><h2>Privacy</h2><p>Use approved school channels for sensitive student information.</p><a href="{{ route('privacy') }}">Privacy information &rarr;</a></div>
</div>
<div class="contact-shell contact-footer__bottom">&copy; {{ now()->year }} {{ config('nacs.short_name') }}</div>
</footer>
</body>
</html>