<?php

namespace App\Http\Controllers;

use App\Models\RegistrationInvitation;
use App\Models\Student;
use App\Services\RegistrationInvitationService;
use App\Support\StrongPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationVerificationController extends Controller
{
    public function showPassword(string $token): View|RedirectResponse
    {
        $invitation = $this->resolve($token);

        if ($invitation->password_set_at) {
            return redirect()->route('registration.otp.show', ['token' => $token]);
        }

        return view('auth.registration-password', [
            'maskedEmail' => $this->maskEmail($invitation->user->email),
            'registrationToken' => $token,
        ]);
    }

    public function storePassword(
        Request $request,
        string $token,
        RegistrationInvitationService $service
    ): RedirectResponse {
        $invitation = $this->resolve($token);
        $user = $invitation->user;

        if ($invitation->password_set_at) {
            return redirect()->route('registration.otp.show', ['token' => $token]);
        }

        $studentNumber = $user->role === 'student'
            ? Student::query()->where('user_id', $user->id)->value('student_number')
            : null;

        $data = $request->validate([
            'password' => StrongPassword::rules([
                $user->name,
                $user->email,
                $studentNumber,
            ]),
        ]);

        DB::transaction(function () use ($user, $invitation, $data): void {
            $user->forceFill([
                'password' => $data['password'],
                'password_changed_at' => now(),
                'force_password_reset' => false,
            ])->save();

            $invitation->forceFill([
                'password_set_at' => now(),
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
            ])->save();
        });

        try {
            $service->sendOtp($invitation->fresh(['user']), $token);
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('registration.otp.show', ['token' => $token])
                ->withErrors([
                    'otp' => 'Your password was saved, but the verification email could not be sent. Use Resend verification code after the school mail service is available.',
                ]);
        }

        return redirect()
            ->route('registration.otp.show', ['token' => $token])
            ->with('success', 'A 6-digit verification code was sent to '.$this->maskEmail($user->email).'.');
    }

    public function showOtp(string $token): View|RedirectResponse
    {
        $invitation = $this->resolve($token);

        if (! $invitation->password_set_at) {
            return redirect()->route('registration.password.show', ['token' => $token]);
        }

        return view('auth.registration-otp', [
            'maskedEmail' => $this->maskEmail($invitation->user->email),
            'registrationToken' => $token,
        ]);
    }

    public function verifyOtp(
        Request $request,
        string $token,
        RegistrationInvitationService $service
    ): RedirectResponse {
        $invitation = $this->resolve($token);

        if (! $invitation->password_set_at) {
            return redirect()->route('registration.password.show', ['token' => $token]);
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if (! $service->otpMatches($invitation, $data['otp'])) {
            return back()->withErrors([
                'otp' => 'The verification code is invalid, expired, or has reached the attempt limit. Request a new code if needed.',
            ]);
        }

        $user = DB::transaction(fn () => $service->complete($invitation->fresh(['user'])));

        return redirect()
            ->route($user->is_admin ? 'admin.login' : 'portal.login')
            ->with('success', 'Email verified. Your NACS-Phil account is now active.');
    }

    public function resendOtp(
        string $token,
        RegistrationInvitationService $service
    ): RedirectResponse {
        $invitation = $this->resolve($token);

        if (! $invitation->password_set_at) {
            return redirect()->route('registration.password.show', ['token' => $token]);
        }

        if (! $service->canResendOtp($invitation)) {
            return back()->withErrors([
                'otp' => 'Please wait at least '.(int) config('registration.resend_cooldown_seconds', 60).' seconds before requesting another code.',
            ]);
        }

        try {
            $service->sendOtp($invitation, $token);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'otp' => 'The verification email could not be sent. Please try again later or contact the school.',
            ]);
        }

        return back()->with('success', 'A new verification code was sent to '.$this->maskEmail($invitation->user->email).'.');
    }

    private function resolve(string $token): RegistrationInvitation
    {
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $token) === 1, 404);

        $invitation = RegistrationInvitation::findActiveByToken($token);

        abort_unless($invitation, 404);
        abort_if(
            ! $invitation->token_expires_at || $invitation->token_expires_at->isPast(),
            410,
            'This registration invitation has expired. Ask the school to send a new invitation.'
        );

        return $invitation;
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');

        if ($domain === '') {
            return 'your registered email';
        }

        $visible = mb_substr($local, 0, min(2, mb_strlen($local)));

        return $visible.str_repeat('*', max(2, mb_strlen($local) - mb_strlen($visible))).'@'.$domain;
    }
}
