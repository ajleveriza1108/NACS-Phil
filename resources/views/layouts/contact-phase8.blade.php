<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#072b55">
<title>@yield('title','Contact') | {{ config('nacs.short_name') }}</title><meta name="description" content="Contact the NACS-Phil school office or submit a general school inquiry.">
<link rel="stylesheet" href="{{ asset('assets/phase8-contact/contact.css') }}"><link rel="stylesheet" href="{{ asset('assets/phase11-unified/public-theme.css') }}">
<script src="{{ asset('assets/phase8-contact/contact.js') }}" defer></script><script src="{{ asset('assets/phase11-unified/public-theme.js') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('assets/phase12-school/backend-public.css') }}">
    @include('partials.seo-meta')
    <link rel="stylesheet" href="{{ asset('assets/phase17-theme/site.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase18-consistency/site-consistency.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/phase20-admissions-contact/fidelity.css') }}">
</head>
<body class="contact-phase8">
@include('partials.public-header', ['mainId' => 'contact-main'])
<main id="contact-main">@yield('content')</main>
@include('partials.public-footer')
</body></html>