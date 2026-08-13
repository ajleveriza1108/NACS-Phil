<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' | ' : '' }}{{ config('nacs.short_name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Official school information, programs, admissions, announcements, and events for ' . config('nacs.short_name') . '.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3">Skip to content</a>

    <div class="bg-nacs-900 px-4 py-2 text-center text-sm font-semibold text-white">
        Development website: official school information and photographs remain subject to school approval.
    </div>

    <header class="sticky top-0 z-40 border-b border-emerald-950/10 bg-[#fffdf8]/95 backdrop-blur">
        <div class="page-shell flex min-h-20 items-center justify-between gap-5">
            <a href="{{ route('home') }}" class="focus-ring flex items-center gap-3 rounded-xl" aria-label="{{ config('nacs.short_name') }} home">
                <img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" class="h-12 w-12">
                <span>
                    <strong class="block font-serif text-xl text-nacs-900">{{ config('nacs.short_name') }}</strong>
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500">Christian Education</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Primary navigation">
                @foreach ([
                    'home' => 'Home', 'about' => 'About', 'programs' => 'Programs', 'admissions' => 'Admissions',
                    'announcements.index' => 'News', 'events.index' => 'Events', 'gallery.index' => 'Gallery', 'contact' => 'Contact'
                ] as $routeName => $label)
                    <a href="{{ route($routeName) }}" class="focus-ring rounded-lg px-3 py-2 text-sm font-semibold {{ request()->routeIs($routeName) ? 'bg-nacs-100 text-nacs-900' : 'text-gray-700 hover:bg-white hover:text-nacs-700' }}">{{ $label }}</a>
                @endforeach
            </nav>

            <button type="button" data-menu-toggle aria-expanded="false" class="focus-ring rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-bold lg:hidden">Menu</button>
        </div>
        <nav data-mobile-menu class="hidden border-t border-gray-200 bg-white lg:hidden" aria-label="Mobile navigation">
            <div class="page-shell grid gap-1 py-3">
                @foreach (['home' => 'Home', 'about' => 'About', 'programs' => 'Programs', 'admissions' => 'Admissions', 'announcements.index' => 'News', 'events.index' => 'Events', 'gallery.index' => 'Gallery', 'contact' => 'Contact'] as $routeName => $label)
                    <a href="{{ route($routeName) }}" class="rounded-lg px-4 py-3 font-semibold hover:bg-nacs-50">{{ $label }}</a>
                @endforeach
            </div>
        </nav>
    </header>

    @if (session('success'))
        <div class="page-shell mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-900" role="status">{{ session('success') }}</div>
    @endif

    <main id="main-content">
        @yield('content')
    </main>

    <footer class="mt-20 bg-nacs-900 text-emerald-50">
        <div class="page-shell grid gap-10 py-14 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" class="h-12 w-12 rounded-full bg-white p-1">
                    <div><strong class="font-serif text-xl">{{ config('nacs.short_name') }}</strong><p class="text-sm text-emerald-100">{{ config('nacs.tagline') }}</p></div>
                </div>
                <p class="mt-5 max-w-sm text-sm leading-7 text-emerald-100">A school website foundation designed to communicate with truth, order, warmth, and careful protection of children.</p>
            </div>
            <div>
                <h2 class="font-bold text-white">Contact</h2>
                <p class="mt-4 text-sm leading-7 text-emerald-100">{{ config('nacs.address') }}</p>
                @if(config('nacs.phone'))<p class="text-sm text-emerald-100">{{ config('nacs.phone') }}</p>@endif
                @if(config('nacs.email'))<p class="text-sm text-emerald-100">{{ config('nacs.email') }}</p>@endif
            </div>
            <div>
                <h2 class="font-bold text-white">Important links</h2>
                <div class="mt-4 grid gap-2 text-sm">
                    <a href="{{ route('admissions') }}" class="hover:text-white">Admissions</a>
                    <a href="{{ route('privacy') }}" class="hover:text-white">Privacy and child protection</a>
                    <a href="{{ config('nacs.facebook_url') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white">Official Facebook page</a>
                    <a href="{{ url('/admin') }}" class="hover:text-white">Staff administration</a>
                </div>
            </div>
        </div>
        <div class="border-t border-emerald-800/80 py-5 text-center text-xs text-emerald-200">&copy; {{ now()->year }} {{ config('nacs.name') }}. Development content must be reviewed before public launch.</div>
    </footer>
</body>
</html>
