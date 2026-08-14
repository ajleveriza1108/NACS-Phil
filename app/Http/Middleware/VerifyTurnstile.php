<?php

namespace App\Http\Middleware;

use App\Support\TurnstileVerifier;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurnstile
{
    public function __construct(private readonly TurnstileVerifier $verifier)
    {
    }

    public function handle(Request $request, Closure $next, string $action): Response
    {
        if (! config('services.turnstile.enabled')) {
            return $next($request);
        }

        $token = trim((string) $request->input('cf-turnstile-response', ''));

        if ($token === '') {
            return $this->reject(
                $request,
                'Please complete the security verification and try again.'
            );
        }

        $result = $this->verifier->verify($token, $action);

        if (! $result['passed']) {
            Log::warning('Turnstile verification rejected a protected form submission.', [
                'action' => $action,
                'reason' => $result['reason'],
            ]);

            return $this->reject(
                $request,
                $result['reason'] === 'verification-unavailable'
                    ? 'Security verification is temporarily unavailable. Please try again in a moment.'
                    : 'Security verification was not accepted. Please try again.'
            );
        }

        return $next($request);
    }

    private function reject(Request $request, string $message): RedirectResponse
    {
        return back()
            ->withInput($request->except([
                '_token',
                'password',
                'access_code',
                'cf-turnstile-response',
            ]))
            ->withErrors(['turnstile' => $message]);
    }
}
