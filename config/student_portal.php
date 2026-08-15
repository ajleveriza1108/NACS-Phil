<?php

return [
    'school_email_domain' => env('NACS_SCHOOL_EMAIL_DOMAIN'),

    'documents' => [
        'storage' => env('NACS_STUDENT_DOCUMENT_STORAGE', 'external'),
        'provider' => env('NACS_STUDENT_DOCUMENT_PROVIDER', 'google_drive'),
        'google_shared_drive_id' => env('NACS_GOOGLE_SHARED_DRIVE_ID'),
        'allow_local_fallback' => false,
    ],
];
