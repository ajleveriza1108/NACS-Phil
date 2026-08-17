<?php

return [
    'logging' => [
        'channel' => env('NACS_SECURITY_LOG_CHANNEL', 'security'),
        'http_security_responses' => env('NACS_SECURITY_LOG_HTTP_RESPONSES', true),
    ],

    'data_classification' => [
        'public' => [
            'news',
            'events',
            'approved_gallery_media',
            'published_school_documents',
        ],
        'internal' => [
            'staff_workflow_metadata',
            'non-public_content_drafts',
        ],
        'confidential' => [
            'student_profiles',
            'grades',
            'attendance',
            'guardian_information',
        ],
        'highly_confidential' => [
            'birth_certificates',
            'student_private_documents',
            'finance_records',
            'payment_transaction_metadata',
            'authentication_secrets',
            'recovery_codes',
        ],
    ],

    'privileged_2fa' => [
        'required' => filter_var(env('NACS_PRIVILEGED_2FA_REQUIRED', false), FILTER_VALIDATE_BOOL),
        'roles' => ['super_admin', 'principal'],
        'activation_requires' => [
            'tested recovery procedure',
            'administrator training',
            'verified authenticator enrollment',
        ],
    ],
    'future' => [
        'mobile_api' => [
            'status' => 'future_only',
            'enabled' => false,
            'requirements' => ['versioned API', 'token authentication', 'shared authorization policies', 'per-user rate limits'],
        ],
        'live_payments' => [
            'status' => 'future_only',
            'enabled' => false,
            'requirements' => ['selected payment gateway', 'signed webhooks', 'idempotency', 'no card credential storage'],
        ],
        'ai_generation' => [
            'status' => 'future_only',
            'enabled' => false,
            'requirements' => ['per-user quota', 'rate limit', 'cost limit', 'prompt/data classification rules'],
        ],
    ],

    'manual_production_gates' => [
        'TLS certificate and HTTPS redirect verified on the real host',
        'Database port is not publicly reachable',
        'Production secrets are supplied outside source control',
        'Backups and restore procedure are tested',
        'Centralized log retention and alert delivery are configured',
        'WAF/CDN abuse controls are configured where supported',
        'Production access review and independent VAPT are completed',
    ],
];
