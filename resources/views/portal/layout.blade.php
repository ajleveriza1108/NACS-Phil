<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#071f3d">
    <title>{{ isset($title) ? $title.' | ' : '' }}NACS-Phil Student & Parent Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/phase41-sis/sis.css') }}">
</head>
<body class="sis-body">
<header class="sis-portal-header">
    <a href="{{ route('home') }}" class="sis-portal-brand">
        <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="46" height="46">
        <span><strong>NACS-Phil</strong><small>Student &amp; Parent Portal</small></span>
    </a>

    @auth
        @if(auth()->user()->is_admin !== true && in_array(auth()->user()->role, ['student','parent'], true))
            <div class="sis-portal-user">
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </div>
        @endif
    @endauth
</header>

<main class="sis-portal-main">
    @if(session('success'))
        <div class="sis-alert sis-alert--success" role="status">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="sis-alert sis-alert--error" role="alert">
            <strong>Please check these items:</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
