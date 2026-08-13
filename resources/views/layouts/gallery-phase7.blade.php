<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#061d3b"><title>@yield('title','Gallery') | {{ config('nacs.short_name') }}</title>
<link rel="stylesheet" href="{{ asset('assets/phase7-gallery/gallery.css') }}"><script src="{{ asset('assets/phase7-gallery/gallery.js') }}" defer></script>
</head><body>
<a href="#gallery-main" class="g-skip">Skip to content</a>
<div class="g-dev"><div class="g-shell"><i></i>Development preview â€” publish only school-approved photographs with appropriate authorization and consent.</div></div>
<header class="g-header" data-g-header><div class="g-shell g-head-inner">
<a href="{{ route('home') }}" class="g-brand"><span><img src="{{ asset('images/nacs-development-mark.svg') }}" alt=""></span><b>{{ config('nacs.short_name') }}</b></a>
<nav class="g-nav"><a href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a class="active" href="{{ route('gallery.index') }}">Gallery</a><a href="{{ route('contact') }}">Contact</a></nav>
<button type="button" class="g-menu" data-g-menu aria-expanded="false" aria-controls="g-mobile"><span></span><span></span><span></span></button>
</div><nav id="g-mobile" class="g-mobile" data-g-mobile hidden aria-label="Mobile navigation"><div class="g-shell"><a href="{{ route('home') }}">Home</a><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a class="active" href="{{ route('gallery.index') }}">Gallery</a><a href="{{ route('contact') }}">Contact</a></div></nav></header>
<main id="gallery-main">@yield('content')</main>
<footer class="g-footer"><div class="g-shell g-footer-grid"><div><b>{{ config('nacs.short_name') }}</b><p>Faith. Character. Excellence.</p></div><div><h2>Updates</h2><a href="{{ route('announcements.index') }}">News</a><a href="{{ route('events.index') }}">Events</a><a href="{{ route('gallery.index') }}">Gallery</a></div><div><h2>Explore</h2><a href="{{ route('about') }}">About</a><a href="{{ route('programs') }}">Programs</a><a href="{{ route('admissions') }}">Admissions</a></div><div><h2>Privacy</h2><a href="{{ route('privacy') }}">Privacy information &rarr;</a></div></div></footer>
<div class="g-lightbox" data-g-lightbox hidden role="dialog" aria-modal="true" aria-labelledby="g-lightbox-title">
<button type="button" class="g-close" data-g-close aria-label="Close image viewer">&times;</button>
<button type="button" class="g-prev" data-g-prev aria-label="Previous image">&larr;</button>
<div class="g-panel"><img src="" alt="" data-g-image><div class="g-caption"><small data-g-category></small><h2 id="g-lightbox-title" data-g-title></h2><p data-g-caption></p><small data-g-credit></small></div></div>
<button type="button" class="g-next" data-g-next aria-label="Next image">&rarr;</button>
</div>
</body></html>