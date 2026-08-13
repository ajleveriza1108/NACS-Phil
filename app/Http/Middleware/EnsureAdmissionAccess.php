<?php

namespace App\Http\Middleware;

use App\Models\AdmissionApplication;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmissionAccess
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $application = $request->route('application');

        $reference = $application instanceof AdmissionApplication
            ? $application->reference_code
            : (string) $application;

        $verifiedReference = (string) $request->session()->get('admission_portal.reference_code', '');
        $verifiedAt = (int) $request->session()->get('admission_portal.verified_at', 0);
        $ttlSeconds = 30 * 60;

        if (
            $reference === ''
            || ! hash_equals($reference, $verifiedReference)
            || $verifiedAt < (time() - $ttlSeconds)
        ) {
            $request->session()->forget('admission_portal');

            return redirect()
                ->route('admissions.track')
                ->withErrors([
                    'reference_code' => 'Enter the application reference and access code to continue.',
                ]);
        }

        $request->session()->put('admission_portal.verified_at', time());

        if ($application instanceof AdmissionApplication) {
            $application->forceFill(['last_viewed_at' => now()])->saveQuietly();
        }

        return $next($request);
    }
}