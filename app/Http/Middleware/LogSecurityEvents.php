<?php

namespace App\Http\Middleware;

use App\Support\SecurityEventLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogSecurityEvents
{
    public function __construct(private readonly SecurityEventLogger $security)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('nacs_security.logging.http_security_responses', true)) {
            return $response;
        }

        $status = $response->getStatusCode();

        if (in_array($status, [401, 403, 419, 429], true) || $status >= 500) {
            $this->security->record(
                $request,
                'http.security_response',
                $status >= 500 ? 'error' : 'warning',
                ['status' => $status]
            );
        }

        return $response;
    }
}
