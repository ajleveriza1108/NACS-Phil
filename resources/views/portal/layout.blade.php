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
    <link rel="stylesheet" href="{{ asset('assets/current/media/2c79c4f7f972-auth.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/current/media/f11cb7c5e172-sis.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/current/media/dcd597a81645-motion.css') }}">
    <script src="{{ asset('assets/current/media/b806be84babf-motion.js') }}" defer></script>
</head>
<body class="nacs-auth-body nacs-auth-body--portal">
    <a href="#portal-main" class="nacs-auth-skip">Skip to portal content</a>

    <header class="nacs-auth-topbar">
        <div class="nacs-auth-topbar__inner">
            <a href="{{ route('home') }}" class="nacs-auth-topbar__brand">
                <img src="{{ \App\Models\SchoolSetting::logoUrl() }}" alt="{{ \App\Models\SchoolSetting::logoAlt() }}" width="48" height="48">
                <span>
                    <strong>NACS-Phil</strong>
                    <small>Student &amp; Parent Portal</small>
                </span>
            </a>

            @auth
                @if(auth()->user()->is_admin !== true && in_array(auth()->user()->role, ['student','parent'], true))
                    <div class="nacs-auth-topbar__user">
                        <span>{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('portal.logout') }}">
                            @csrf
                            <button type="submit">Sign out</button>
                        </form>
                    </div>
                @endif
            @endauth
        </div>
    </header>

    <main id="portal-main" class="nacs-auth-shell nacs-auth-shell--portal">
        @if(session('success'))
            <div class="nacs-auth-alert nacs-auth-alert--success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="nacs-auth-alert nacs-auth-alert--error" role="alert">
                <strong>Please review the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
