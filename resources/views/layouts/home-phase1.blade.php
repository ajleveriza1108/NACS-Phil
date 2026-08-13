<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#072b55">
    <title>Home | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="Discover {{ config('nacs.short_name') }} programs, Christian education, school announcements, events, admissions information, and campus life.">
    <link rel="stylesheet" href="{{ asset('assets/phase1-home/home.css') }}">
    <script src="{{ asset('assets/phase1-home/home.js') }}" defer></script>
</head>
<body class="nacs-home-phase1">
    <a class="skip-link" href="#main-content">Skip to content</a>

    <div class="development-strip">
        <div class="home-shell development-strip__inner">
            <span class="development-strip__dot" aria-hidden="true"></span>
            <span>Development preview - official text, photographs, and contact details remain subject to school approval.</span>
        </div>
    </div>

    <header class="site-header" data-site-header>
        <div class="home-shell site-header__inner">
            <a href="{{ route('home') }}" class="brand" aria-label="{{ config('nacs.short_name') }} home">
                <span class="brand__mark">
                    <img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="52" height="52">
                </span>
                <span class="brand__copy">
                    <strong>{{ config('nacs.short_name') }}</strong>
                    <small>Noel Academy Christian of Sariaya Philippines</small>
                </span>
            </a>

            <nav class="desktop-nav" aria-label="Primary navigation">
                <a class="is-active" href="{{ route('home') }}">Home</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('programs') }}">Programs</a>
                <a href="{{ route('admissions') }}">Admissions</a>
                <a href="{{ route('announcements.index') }}">News</a>
                <a href="{{ route('events.index') }}">Events</a>
                <a href="{{ route('gallery.index') }}">Gallery</a>
                <a href="{{ route('contact') }}">Contact</a>
            </nav>

            <div class="site-header__actions">
                <a class="button button--primary button--compact desktop-enroll" href="{{ route('admissions') }}">
                    <span>Enroll Now</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <button class="menu-button" type="button" data-menu-button aria-expanded="false" aria-controls="home-mobile-menu">
                    <span class="sr-only">Open navigation</span>
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>

        <nav class="mobile-nav" id="home-mobile-menu" data-mobile-menu hidden aria-label="Mobile navigation">
            <div class="home-shell mobile-nav__inner">
                <a class="is-active" href="{{ route('home') }}">Home</a>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('programs') }}">Programs</a>
                <a href="{{ route('admissions') }}">Admissions</a>
                <a href="{{ route('announcements.index') }}">News</a>
                <a href="{{ route('events.index') }}">Events</a>
                <a href="{{ route('gallery.index') }}">Gallery</a>
                <a href="{{ route('contact') }}">Contact</a>
                <a class="button button--primary" href="{{ route('admissions') }}">Enroll Now <span aria-hidden="true">&rarr;</span></a>
            </div>
        </nav>
    </header>

    @if (session('success'))
        <div class="home-shell flash-message" role="status">{{ session('success') }}</div>
    @endif

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="home-footer">
        <div class="home-shell home-footer__grid">
            <section class="home-footer__brand">
                <div class="brand brand--footer">
                    <span class="brand__mark brand__mark--footer">
                        <img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="58" height="58">
                    </span>
                    <span class="brand__copy">
                        <strong>{{ config('nacs.short_name') }}</strong>
                        <small>Noel Academy Christian of Sariaya Philippines</small>
                    </span>
                </div>
                <p>{{ $homeContent['footer_tagline'] }}</p>
                <span class="home-footer__verse">{{ $homeContent['footer_tagline'] }}</span>
            </section>

            <section>
                <h2>Contact Us</h2>
                <div class="footer-links">
                    @if(!empty($homeContent['contact_phone']))<span>{{ $homeContent['contact_phone'] }}</span>@endif
                    @if(!empty($homeContent['contact_email']))<a href="mailto:{{ $homeContent['contact_email'] }}">{{ $homeContent['contact_email'] }}</a>@endif
                    <a href="{{ route('contact') }}">Send an inquiry</a>
                </div>
            </section>

            <section>
                <h2>Our Location</h2>
                <p>{{ $homeContent['contact_address'] ?: config('nacs.address') }}</p>
                <a class="footer-inline-link" href="{{ route('contact') }}">View contact page &rarr;</a>
            </section>

            <section>
                <h2>Quick Links</h2>
                <div class="footer-links">
                    <a href="{{ route('about') }}">About Us</a>
                    <a href="{{ route('admissions') }}">Admissions</a>
                    <a href="{{ route('programs') }}">Programs</a>
                    <a href="{{ route('announcements.index') }}">News &amp; Events</a>
                    <a href="{{ route('privacy') }}">Privacy</a>
                </div>
            </section>

            <section>
                <h2>Find Us</h2>
                <div class="social-row">
                    @if(!empty($homeContent['facebook_url']))
                        <a href="{{ $homeContent['facebook_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook">f</a>
                    @endif
                    <a href="{{ route('contact') }}" aria-label="Contact NACS-Phil">@</a>
                </div>
                <div class="map-card" aria-hidden="true">
                    <span class="map-card__road map-card__road--one"></span>
                    <span class="map-card__road map-card__road--two"></span>
                    <span class="map-card__pin"></span>
                    <strong>NACS-Phil</strong>
                    <small>Sariaya, Quezon</small>
                </div>
            </section>
        </div>

        <div class="home-shell home-footer__bottom">
            <span>&copy; {{ now()->year }} {{ config('nacs.short_name') }}. All rights reserved.</span>
            <span>Development content must be reviewed before public launch.</span>
        </div>
    </footer>
</body>
</html>