<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#072b55">
<title>@yield('title','Admissions') | {{ config('nacs.short_name') }}</title>
<link rel="stylesheet" href="{{ asset('assets/phase9c-admissions/portal.css') }}"><link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
<script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
    <link rel="stylesheet" href="{{ asset('assets/phase17-theme/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase18-consistency/site-consistency.css') }}">
</head>
<body class="adm9-body">
@include('partials.public-header', ['mainId' => 'main'])
<main id="main" class="adm9-main">@yield('content')</main>
@include('partials.public-footer')
</body></html>