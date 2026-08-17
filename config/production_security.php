<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Real production-host evidence gates
    |--------------------------------------------------------------------------
    |
    | These values record independently verified evidence. They do not create
    | TLS, firewall rules, backups, WAF rules, monitoring, access reviews, or
    | penetration tests. Keep every flag false until the real host proves it.
    |
    */
    'host_gates' => [
        'tls_https_verified' => filter_var(env('NACS_PROD_TLS_HTTPS_VERIFIED', false), FILTER_VALIDATE_BOOL),
        'database_private_verified' => filter_var(env('NACS_PROD_DATABASE_PRIVATE_VERIFIED', false), FILTER_VALIDATE_BOOL),
        'backup_restore_verified' => filter_var(env('NACS_PROD_BACKUP_RESTORE_VERIFIED', false), FILTER_VALIDATE_BOOL),
        'central_logging_verified' => filter_var(env('NACS_PROD_CENTRAL_LOGGING_VERIFIED', false), FILTER_VALIDATE_BOOL),
        'waf_cdn_verified' => filter_var(env('NACS_PROD_WAF_CDN_VERIFIED', false), FILTER_VALIDATE_BOOL),
        'access_review_verified' => filter_var(env('NACS_PROD_ACCESS_REVIEW_VERIFIED', false), FILTER_VALIDATE_BOOL),
        'vapt_verified' => filter_var(env('NACS_PROD_VAPT_VERIFIED', false), FILTER_VALIDATE_BOOL),
    ],
];
