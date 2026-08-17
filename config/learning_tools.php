<?php

return [
    'dictionary' => [
        'enabled' => filter_var(env('NACS_DICTIONARY_ENABLED', true), FILTER_VALIDATE_BOOL),
        'endpoint' => rtrim((string) env('NACS_DICTIONARY_ENDPOINT', 'https://api.dictionaryapi.dev/api/v2/entries/en'), '/'),
        'timeout_seconds' => max(2, min(15, (int) env('NACS_DICTIONARY_TIMEOUT', 6))),
        'cache_seconds' => max(60, (int) env('NACS_DICTIONARY_CACHE_SECONDS', 86400)),
    ],

    'grammar' => [
        'enabled' => filter_var(env('NACS_GRAMMAR_ENABLED', true), FILTER_VALIDATE_BOOL),
        'endpoint' => (string) env('NACS_GRAMMAR_ENDPOINT', 'https://api.languagetool.org/v2/check'),
        'timeout_seconds' => max(2, min(20, (int) env('NACS_GRAMMAR_TIMEOUT', 8))),
        'max_chars' => max(250, min(10000, (int) env('NACS_GRAMMAR_MAX_CHARS', 2000))),
        'languages' => [
            'en-US' => 'English (US)',
            'en-GB' => 'English (UK)',
        ],
    ],
];
