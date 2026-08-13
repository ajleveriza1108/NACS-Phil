<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#072b55">
<title>@yield('title','Gallery') | {{ config('nacs.short_name') }}</title>
<link rel="stylesheet" href="{{ asset('assets/phase7-gallery/gallery.css') }}"><link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
<script src="{{ asset('assets/phase7-gallery/gallery.js') }}" defer></script><script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
</head>
<body class="gallery-phase7">
@include('partials.public-header', ['mainId' => 'gallery-main'])
<main id="gallery-main">@yield('content')</main>
@include('partials.public-footer')
<div class="g-lightbox" data-g-lightbox hidden role="dialog" aria-modal="true" aria-labelledby="g-lightbox-title">
<button type="button" class="g-close" data-g-close aria-label="Close image viewer">&times;</button>
<button type="button" class="g-prev" data-g-prev aria-label="Previous image">&larr;</button>
<div class="g-panel"><img src="" alt="" data-g-image><div class="g-caption"><small data-g-category></small><h2 id="g-lightbox-title" data-g-title></h2><p data-g-caption></p><small data-g-credit></small></div></div>
<button type="button" class="g-next" data-g-next aria-label="Next image">&rarr;</button>
</div>
</body></html>