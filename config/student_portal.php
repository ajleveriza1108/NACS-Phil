<?php

return [
    'school_email_domain' => env('NACS_SCHOOL_EMAIL_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Private student profile photos
    |--------------------------------------------------------------------------
    |
    | Small profile images can live on Laravel's private disk and are never
    | exposed through /public/storage. They are delivered only after student
    | authorization checks. The disk can later be changed to approved object
    | storage without changing the student record contract.
    |
    */
    'profile_photo' => [
        'disk' => env('NACS_STUDENT_PHOTO_DISK', 'local'),
        'max_kb' => 1024,
        'min_width' => 400,
        'min_height' => 400,
        'preferred_ratio' => '1:1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Larger confidential student documents
    |--------------------------------------------------------------------------
    |
    | Birth certificates and other identity documents remain external/private
    | references by default so the website host is not used as a document vault.
    */
    'documents' => [
        'storage' => env('NACS_STUDENT_DOCUMENT_STORAGE', 'external'),
        'provider' => env('NACS_STUDENT_DOCUMENT_PROVIDER', 'google_drive'),
        'google_shared_drive_id' => env('NACS_GOOGLE_SHARED_DRIVE_ID'),
        'max_kb' => 10240,
        'allow_local_fallback' => false,
    ],
];
