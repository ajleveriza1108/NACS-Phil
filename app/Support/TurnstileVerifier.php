<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

final class TurnstileVerifier
{
    /**
     * @return array{passed:bool,reason:string}
     */
    public function verify(string $token, string $expectedAction): array
    {
        $secret = trim((string) config('services.turnstile.secret_key'));
        $verifyUrl = (string) config(
            'services.turnstile.verify_url',
            'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        );

        if ($secret === '') {
            return ['passed' => false, 'reason' => 'verification-unavailable'];
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(7)
                ->post($verifyUrl, [
                    'secret' => $secret,
                    'response' => $token,
                ]);
        } catch (Throwable) {
            return ['passed' => false, 'reason' => 'verification-unavailable'];
        }

        if (! $response->successful()) {
            return ['passed' => false, 'reason' => 'verification-unavailable'];
        }

        $data = $response->json();

        if (! is_array($data) || ($data['success'] ?? false) !== true) {
            return ['passed' => false, 'reason' => 'challenge-rejected'];
        }

        if (($data['action'] ?? '') !== $expectedAction) {
            return ['passed' => false, 'reason' => 'action-mismatch'];
        }

        $expectedHostname = strtolower(trim((string) config('services.turnstile.expected_hostname')));
        $actualHostname = strtolower(trim((string) ($data['hostname'] ?? '')));

        if ($expectedHostname !== '' && $actualHostname !== $expectedHostname) {
            return ['passed' => false, 'reason' => 'hostname-mismatch'];
        }

        return ['passed' => true, 'reason' => 'verified'];
    }
}
