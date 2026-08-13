<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#072b55">
<title>About | {{ config('nacs.short_name') }}</title><meta name="description" content="Learn about {{ config('nacs.short_name') }}, its school story, mission, vision, Christian foundation, values, and leadership.">
<link rel="stylesheet" href="{{ asset('assets/phase2-about/about.css') }}"><link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
<script src="{{ asset('assets/phase2-about/about.js') }}" defer></script><script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
    <link rel="stylesheet" href="{{ asset('assets/phase17-theme/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase18-consistency/site-consistency.css') }}">
</head>
<body class="about-phase2">
@include('partials.public-header', ['mainId' => 'about-main'])
<main id="about-main">@yield('content')</main>
@include('partials.public-footer')
</body></html>