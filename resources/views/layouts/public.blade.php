<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#072b55">
<title>{{ isset($title) ? $title . ' | ' : '' }}{{ config('nacs.short_name') }}</title>
<meta name="description" content="@yield('meta_description', 'Official school information, programs, admissions, announcements, and events for ' . config('nacs.short_name') . '.')">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
<script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
</head>
<body class="nacs11-public">
@include('partials.public-header', ['mainId' => 'main-content'])
@if (session('success'))<div class="nacs11-shell" style="margin-top:18px" role="status">{{ session('success') }}</div>@endif
<main id="main-content">@yield('content')</main>
@include('partials.public-footer')
</body></html>