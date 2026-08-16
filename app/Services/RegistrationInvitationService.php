<?php

namespace App\Services;

use App\Models\RegistrationInvitation;
use App\Models\User;
use App\Notifications\RegistrationInvitationNotification;
use App\Notifications\RegistrationOtpNotification;
use Illuminate\Support\Facades\Hash;

class RegistrationInvitationService
{
    public function issue(User $user, ?User $actor = null): void
    {
        if ($user->email_verified_at !== null || $user->is_active === true) {
            throw new \LogicException('Only inactive, unverified accounts may receive a registration invitation.');
        }

        $plainToken = bin2hex(random_bytes(32));

        $user->forceFill([
            'password' => Hash::make(bin2hex(random_bytes(32))),
            'is_active' => false,
            'email_verified_at' => null,
            'password_changed_at' => null,
            'force_password_reset' => false,
        ])->save();

        RegistrationInvitation::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'token_hash' => hash('sha256', $plainToken),
                'token_expires_at' => now()->addHours((int) config('registration.invitation_hours', 48)),
                'password_set_at' => null,
                'otp_hash' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'otp_sent_at' => null,
                'completed_at' => null,
                'created_by' => $actor?->id,
            ]
        );

        $user->notify(new RegistrationInvitationNotification(
            route('registration.password.show', ['token' => $plainToken]),
            $this->accountLabel($user),
            (int) config('registration.invitation_hours', 48),
        ));
    }

    public function sendOtp(RegistrationInvitation $invitation, string $plainToken): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $invitation->forceFill([
            'otp_hash' => $this->otpHash($code),
            'otp_expires_at' => now()->addMinutes((int) config('registration.otp_minutes', 10)),
            'otp_attempts' => 0,
            'otp_sent_at' => now(),
        ])->save();

        $invitation->user->notify(new RegistrationOtpNotification(
            $code,
            route('registration.otp.show', ['token' => $plainToken]),
            (int) config('registration.otp_minutes', 10),
        ));
    }

    public function otpMatches(RegistrationInvitation $invitation, string $code): bool
    {
        $maxAttempts = (int) config('registration.otp_max_attempts', 5);

        if (
            ! $invitation->otp_hash
            || ! $invitation->otp_expires_at
            || $invitation->otp_expires_at->isPast()
            || $invitation->otp_attempts >= $maxAttempts
        ) {
            return false;
        }

        if (! hash_equals($invitation->otp_hash, $this->otpHash($code))) {
            $invitation->increment('otp_attempts');
            return false;
        }

        return true;
    }

    public function canResendOtp(RegistrationInvitation $invitation): bool
    {
        if (! $invitation->otp_sent_at) {
            return true;
        }

        return $invitation->otp_sent_at
            ->copy()
            ->addSeconds((int) config('registration.resend_cooldown_seconds', 60))
            ->isPast();
    }

    public function complete(RegistrationInvitation $invitation): User
    {
        $user = $invitation->user;

        $user->forceFill([
            'is_active' => true,
            'email_verified_at' => now(),
            'password_changed_at' => $user->password_changed_at ?: now(),
            'force_password_reset' => false,
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();

        $invitation->forceFill([
            'token_hash' => null,
            'otp_hash' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
            'completed_at' => now(),
        ])->save();

        return $user;
    }

    private function otpHash(string $code): string
    {
        $key = (string) config('app.key');

        if ($key === '') {
            throw new \RuntimeException('APP_KEY is required before registration verification can be used.');
        }

        return hash_hmac('sha256', $code, $key);
    }

    private function accountLabel(User $user): string
    {
        return match ($user->role) {
            'principal' => 'Principal / School Admin account',
            'teacher' => 'Teacher account',
            'student' => 'Student Portal account',
            default => 'school account',
        };
    }
}
