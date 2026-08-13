<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' | ' : '' }}NACS-Phil Content Manager</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/admin-content-manager/manager.css') }}">
    <script src="{{ asset('assets/admin-content-manager/manager.js') }}" defer></script>
</head>
<body class="cm-body">
<div class="cm-app">
    <aside class="cm-sidebar" data-cm-sidebar>
        <div class="cm-sidebar__top">
            <a href="{{ route('admin.dashboard') }}" class="cm-brand">
                <img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" width="48" height="48">
                <span><strong>NACS-Phil</strong><small>Content Manager</small></span>
            </a>

            <button type="button" class="cm-sidebar-close" data-cm-close aria-label="Close menu">&times;</button>
        </div>

        <p class="cm-sidebar-label">Daily posting</p>
        <nav class="cm-nav" aria-label="Content manager">
            <a href="{{ route('admin.dashboard') }}" @class(['is-active' => request()->routeIs('admin.dashboard')])>
                <span class="cm-nav-icon">H</span><span>Home</span>
            </a>
            <a href="{{ route('admin.announcements.index') }}" @class(['is-active' => request()->routeIs('admin.announcements.*')])>
                <span class="cm-nav-icon">N</span><span>Announcements</span>
            </a>
            <a href="{{ route('admin.events.index') }}" @class(['is-active' => request()->routeIs('admin.events.*')])>
                <span class="cm-nav-icon">E</span><span>Events</span>
            </a>
            <a href="{{ route('admin.gallery.index') }}" @class(['is-active' => request()->routeIs('admin.gallery.*')])>
                <span class="cm-nav-icon">P</span><span>Photos</span>
            </a>
        </nav>

        <p class="cm-sidebar-label">Website</p>
        <nav class="cm-nav">
            <a href="{{ route('admin.website-content.edit') }}" @class(['is-active' => request()->routeIs('admin.website-content.*')])>
                <span class="cm-nav-icon">W</span><span>Edit Homepage</span>
            </a>
            <a href="{{ route('admin.about-content.edit') }}" @class(['is-active' => request()->routeIs('admin.about-content.*')])>
                <span class="cm-nav-icon">A</span><span>Edit About</span>
            </a>
<a href="{{ route('admin.programs-content.edit') }}" @class(['is-active' => request()->routeIs('admin.programs-content.*')])>
                <span class="cm-nav-icon">P</span><span>Edit Programs</span>
            </a>
<a href="{{ route('admin.admissions-content.edit') }}" @class(['is-active' => request()->routeIs('admin.admissions-content.*')])>
                <span class="cm-nav-icon">D</span><span>Edit Admissions</span>
            </a>
<a href="{{ route('admin.news-content.edit') }}" @class(['is-active' => request()->routeIs('admin.news-content.*')])>
                <span class="cm-nav-icon">N</span><span>Edit News Page</span>
            </a>
            <a href="{{ route('admin.inquiries.index') }}" @class(['is-active' => request()->routeIs('admin.inquiries.*')])>
                <span class="cm-nav-icon">I</span><span>Inquiries</span>
            </a>
            @if(\Illuminate\Support\Facades\Route::has('admin.admissions.index'))
                <a href="{{ route('admin.admissions.index') }}" @class(['is-active' => request()->routeIs('admin.admissions.*')])>
                    <span class="cm-nav-icon">A</span><span>Applications</span>
                </a>
            @endif
        </nav>

        <div class="cm-sidebar__bottom">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="cm-view-site">View public website <span>&nearr;</span></a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="cm-signout">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="cm-main">
        <header class="cm-topbar">
            <div class="cm-topbar__left">
                <button type="button" class="cm-menu-button" data-cm-open aria-label="Open menu"><span></span><span></span><span></span></button>
                <div>
                    <small>School staff area</small>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>
            </div>
            <div class="cm-topbar__help">
                <span class="cm-safe-dot"></span>
                <span>Content changes only. Design and developer settings are protected.</span>
            </div>
        </header>

        <main class="cm-content">
            @if(session('success'))
                <div class="cm-alert cm-alert--success" role="status">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="cm-alert cm-alert--error" role="alert">
                    <strong>Please check these items:</strong>
                    <ul>
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<div class="cm-backdrop" data-cm-backdrop hidden></div>
</body>
</html>