<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#072b55">
    <title>Home | {{ config('nacs.short_name') }}</title>
    <meta name="description" content="Discover {{ config('nacs.short_name') }} programs, Christian education, school announcements, events, admissions information, and campus life.">
    <link rel="stylesheet" href="{{ asset('assets/phase1-home/home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
    <script src="{{ asset('assets/phase1-home/home.js') }}" defer></script>
    <script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
    <link rel="stylesheet" href="{{ asset('assets/phase17-theme/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase18-consistency/site-consistency.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase18-home/home.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase24-release/release-hardening.css') }}">
</head>
<body class="nacs-home-phase1">
    @include('partials.public-header', ['mainId' => 'main-content'])
    @if (session('success'))<div class="home-shell flash-message" role="status">{{ session('success') }}</div>@endif
    <main id="main-content">@yield('content')</main>
    @include('partials.public-footer')
</body>
</html>