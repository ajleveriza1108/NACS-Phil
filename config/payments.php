<?php

return [
    /*
    |--------------------------------------------------------------------------
    | NACS-Phil payment readiness
    |--------------------------------------------------------------------------
    |
    | Phase 47 prepares a provider-neutral transaction ledger only. No card,
    | bank-account, e-wallet credential, or gateway secret is stored in the
    | student database. Live payment routes remain disabled until a production
    | gateway and hosting environment are approved.
    |
    */
    'enabled' => (bool) env('NACS_PAYMENTS_ENABLED', false),
    'provider' => env('NACS_PAYMENT_PROVIDER'),
    'currency' => env('NACS_PAYMENT_CURRENCY', 'PHP'),
    'webhook_secret' => env('NACS_PAYMENT_WEBHOOK_SECRET'),
];
