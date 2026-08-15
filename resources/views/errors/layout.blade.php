<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex,nofollow">
    <meta name="theme-color" content="#071f3d">
    <title>@yield('title') | NACS-Phil</title>
    <link rel="stylesheet" href="/assets/phase42-launch/errors.css">
</head>
<body>
    <a class="nacs42-skip" href="#error-main">Skip to message</a>

    <main id="error-main" class="nacs42-shell">
        <section class="nacs42-card" aria-labelledby="error-title">
            <div class="nacs42-brand" aria-label="NACS-Phil">
                <img src="/assets/phase17-theme/nacs-official-logo.png" alt="NACS-Phil logo" width="68" height="68">
                <div>
                    <strong>NACS-Phil</strong>
                    <span>Noel Academy Christian of Sariaya Philippines, Inc.</span>
                </div>
            </div>

            <div class="nacs42-code">@yield('code')</div>
            <h1 id="error-title">@yield('heading')</h1>
            <p class="nacs42-message">@yield('message')</p>

            <div class="nacs42-actions">
                <a class="nacs42-primary" href="/">Return home</a>
                <a class="nacs42-secondary" href="/admissions">Admissions</a>
                <a class="nacs42-secondary" href="/contact">Contact NACS</a>
            </div>

            <div class="nacs42-help">
                @yield('help')
            </div>
        </section>
    </main>
</body>
</html>
