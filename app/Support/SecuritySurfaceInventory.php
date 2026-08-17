<?php

namespace App\Support;

class SecuritySurfaceInventory
{
    public static function current(): array
    {
        return [
            'public_inquiry' => ['input' => true, 'controls' => ['validation', 'throttle', 'turnstile']],
            'admissions_apply' => ['input' => true, 'controls' => ['validation', 'throttle', 'turnstile']],
            'admissions_track' => ['input' => true, 'controls' => ['validation', 'throttle', 'turnstile', 'access_code']],
            'admissions_documents' => ['upload' => true, 'controls' => ['authorization', 'validation', 'private_storage', 'throttle']],
            'registration_password' => ['input' => true, 'controls' => ['invitation_token', 'strong_password', 'throttle']],
            'registration_otp' => ['input' => true, 'controls' => ['expiry', 'attempt_limit', 'resend_cooldown', 'throttle']],
            'admin_login' => ['input' => true, 'controls' => ['password_hashing', 'account_lock', 'throttle', 'turnstile', 'session_rotation']],
            'admin_two_factor' => ['input' => true, 'controls' => ['totp', 'recovery_codes', 'throttle']],
            'portal_login' => ['input' => true, 'controls' => ['password_hashing', 'account_lock', 'throttle', 'session_rotation']],
            'student_records' => ['protected' => true, 'controls' => ['role_permission', 'relationship_authorization', 'default_deny']],
            'student_profile_photo' => ['upload' => true, 'controls' => ['relationship_authorization', 'image_allowlist', 'size_limit', 'dimension_limit', 'private_storage', 'throttle']],
            'academic_records' => ['protected' => true, 'controls' => ['relationship_authorization', 'official_view_leadership_only', 'throttle']],
            'staff_content_uploads' => ['upload' => true, 'controls' => ['staff_permission', 'validation', 'content_review']],
        ];
    }

    public static function future(): array
    {
        return [
            'mobile_api' => ['status' => 'future_only'],
            'live_payments' => ['status' => 'future_only'],
            'ai_generation' => ['status' => 'future_only'],
        ];
    }
}
