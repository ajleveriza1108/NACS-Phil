<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title','Admissions') | {{ config('nacs.short_name') }}</title>
<link rel="stylesheet" href="{{ asset('assets/phase9c-admissions/portal.css') }}">
</head>
<body class="adm9-body">
<a class="adm9-skip" href="#main">Skip to content</a>
<header class="adm9-header">
<div class="adm9-shell">
<a class="adm9-brand" href="{{ route('admissions') }}"><strong>NACS-Phil</strong><span>Admissions</span></a>
<nav><a href="{{ route('admissions') }}">Admissions Home</a><a href="{{ route('admissions.apply') }}">Apply</a><a href="{{ route('admissions.track') }}">Track Application</a><a href="{{ route('contact') }}">Contact</a></nav>
</div>
</header>
<main id="main" class="adm9-main">@yield('content')</main>
<footer class="adm9-footer"><div class="adm9-shell"><p>Preliminary admissions portal. Do not submit sensitive documents unless the school specifically requests them.</p><a href="{{ route('privacy') }}">Privacy information &rarr;</a></div></footer>
</body>
</html>