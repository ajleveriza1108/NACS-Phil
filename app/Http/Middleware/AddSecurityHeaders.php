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

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Content-Security-Policy' => "frame-ancestors 'self'; base-uri 'self'; object-src 'none'; form-action 'self'",
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
