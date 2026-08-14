<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $scriptSources = [
            "'self'",
            'https://challenges.cloudflare.com',
        ];

        $styleSources = [
            "'self'",
            "'unsafe-inline'",
        ];

        $imageSources = [
            "'self'",
            'data:',
            'https:',
        ];

        $fontSources = [
            "'self'",
            'data:',
        ];

        $connectSources = [
            "'self'",
            'https://challenges.cloudflare.com',
        ];

        if (app()->environment(['local', 'testing'])) {
            foreach (['http://localhost:*', 'http://127.0.0.1:*'] as $localOrigin) {
                $scriptSources[] = $localOrigin;
                $styleSources[] = $localOrigin;
                $imageSources[] = $localOrigin;
                $fontSources[] = $localOrigin;
                $connectSources[] = $localOrigin;
            }

            $connectSources[] = 'ws://localhost:*';
            $connectSources[] = 'ws://127.0.0.1:*';
        }

        $csp = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            'img-src '.implode(' ', $imageSources),
            'font-src '.implode(' ', $fontSources),
            'connect-src '.implode(' ', $connectSources),
            "frame-src 'self' https://challenges.cloudflare.com https://www.facebook.com",
            "media-src 'self' blob:",
        ];

        if ($request->isSecure() && app()->environment('production')) {
            $csp[] = 'upgrade-insecure-requests';
        }

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Content-Security-Policy' => implode('; ', $csp),
        ];

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        if (
            $request->is('admin')
            || $request->is('admin/*')
            || $request->is('admissions/apply')
            || $request->is('admissions/apply/*')
            || $request->is('admissions/track')
            || $request->is('admissions/track/*')
            || $request->is('admissions/status/*')
            || $request->is('admissions/receipt/*')
        ) {
            $response->headers->set('Cache-Control', 'no-store, private, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
