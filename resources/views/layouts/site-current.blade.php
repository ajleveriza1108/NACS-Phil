@php
    $resolvedTitle = $title ?? trim($__env->yieldContent('title'));
    $resolvedTitle = $resolvedTitle !== '' ? $resolvedTitle : null;

    $resolvedDescription = $description ?? trim($__env->yieldContent('meta_description'));
    $resolvedDescription = $resolvedDescription !== ''
        ? $resolvedDescription
        : 'Official school information, programs, admissions, announcements, events, and resources for '.config('nacs.short_name').'.';

    $resolvedBodyClass = trim('nacs-current-site '.($bodyClass ?? 'nacs11-public'));
    $resolvedMainId = $mainId ?? 'main-content';
    $resolvedMainClass = $mainClass ?? '';
    $resolvedBundle = $assetBundle ?? 'public';
    $resolvedUseVite = (bool) ($useVite ?? false);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#072b55">
    <title>{{ $resolvedTitle ? $resolvedTitle.' | ' : '' }}{{ config('nacs.short_name') }}</title>
    <meta name="description" content="{{ $resolvedDescription }}">
    @if($resolvedUseVite)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @if($resolvedBundle === 'learning-tools')
        <link rel="stylesheet" href="{{ asset('assets/current/pages/public.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/current/pages/'.$resolvedBundle.'.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/current/phase56/visual-polish.css') }}">
    @if($resolvedBundle === 'learning-tools')
        <script src="{{ asset('assets/current/pages/public.js') }}" defer></script>
    @endif
    <script src="{{ asset('assets/current/pages/'.$resolvedBundle.'.js') }}" defer></script>
    @include('partials.seo-meta')
</head>
<body class="{{ $resolvedBodyClass }}">
    @include('partials.public-header', ['mainId' => $resolvedMainId])

    @if(session('success'))
        <div class="nacs11-shell" style="margin-top:18px" role="status">{{ session('success') }}</div>
    @endif

    <main id="{{ $resolvedMainId }}" @if($resolvedMainClass !== '') class="{{ $resolvedMainClass }}" @endif>
        @yield('content')
    </main>

    @include('partials.public-footer')

    @if($resolvedBundle === 'gallery')
        <div class="g-lightbox" data-g-lightbox hidden role="dialog" aria-modal="true" aria-labelledby="g-lightbox-title">
            <button type="button" class="g-close" data-g-close aria-label="Close image viewer">&times;</button>
            <button type="button" class="g-prev" data-g-prev aria-label="Previous image">&larr;</button>
            <div class="g-panel">
                <img src="" alt="" data-g-image>
                <div class="g-caption">
                    <small data-g-category></small>
                    <h2 id="g-lightbox-title" data-g-title></h2>
                    <p data-g-caption></p>
                    <small data-g-credit></small>
                </div>
            </div>
            <button type="button" class="g-next" data-g-next aria-label="Next image">&rarr;</button>
        </div>
    @endif
</body>
</html>
