<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Sign In | NACS-Phil</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hero-pattern grid min-h-screen place-items-center px-4 py-10">
    <main class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
        <div class="text-center">
            <img src="{{ asset('images/nacs-development-mark.svg') }}" alt="" class="mx-auto h-20 w-20">
            <p class="eyebrow mt-5">Authorized staff only</p>
            <h1 class="section-title mt-2 text-3xl">NACS-Phil Administration</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Use the administrator account created through <strong>CREATE-ADMIN.bat</strong>.</p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-7 space-y-5">
            @csrf
            <label class="block"><span class="text-sm font-bold">Email address</span><input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-nacs-600 focus:outline-none focus:ring-2 focus:ring-nacs-100"></label>
            <label class="block"><span class="text-sm font-bold">Password</span><input type="password" name="password" required autocomplete="current-password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-nacs-600 focus:outline-none focus:ring-2 focus:ring-nacs-100"></label>
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300"> Keep me signed in on this private computer</label>
            @include('partials.turnstile', ['action' => 'admin_login'])
            <button class="w-full rounded-xl bg-nacs-700 px-5 py-3 font-bold text-white hover:bg-nacs-800">Sign in</button>
        </form>
        <a href="{{ route('home') }}" class="mt-6 block text-center text-sm font-semibold text-nacs-700 hover:underline">Return to public website</a>
    </main>
</body>
</html>
