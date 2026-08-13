<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#072b55">
<title>@yield('title','Events') | {{ config('nacs.short_name') }}</title><meta name="description" content="@yield('meta_description','Upcoming school events and activities at NACS-Phil.')">
<link rel="stylesheet" href="{{ asset('assets/phase6-events/events.css') }}"><link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
<script src="{{ asset('assets/phase6-events/events.js') }}" defer></script><script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
</head>
<body class="events-phase6">
@include('partials.public-header', ['mainId' => 'events-main'])
<main id="events-main">@yield('content')</main>
@include('partials.public-footer')
</body></html>