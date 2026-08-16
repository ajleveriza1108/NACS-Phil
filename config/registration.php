<?php

return [
    'invitation_hours' => (int) env('NACS_REGISTRATION_INVITATION_HOURS', 48),
    'otp_minutes' => (int) env('NACS_REGISTRATION_OTP_MINUTES', 10),
    'otp_max_attempts' => (int) env('NACS_REGISTRATION_OTP_MAX_ATTEMPTS', 5),
    'resend_cooldown_seconds' => (int) env('NACS_REGISTRATION_OTP_RESEND_SECONDS', 60),
];
