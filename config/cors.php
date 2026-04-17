<?php

$corsOrigins = env('CORS_ALLOWED_ORIGINS');
if ($corsOrigins === null || $corsOrigins === '') {
    $allowedOrigins = env('APP_ENV') === 'production' ? ['*'] : ['http://localhost:3000'];
} elseif (trim((string) $corsOrigins) === '*') {
    $allowedOrigins = ['*'];
} else {
    $allowedOrigins = array_values(array_filter(array_map('trim', explode(',', (string) $corsOrigins))));
    if ($allowedOrigins === []) {
        $allowedOrigins = ['http://localhost:3000'];
    }
}

$wildcardOrigins = $allowedOrigins === ['*'];

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * Browsers disallow credentials with Access-Control-Allow-Origin: *.
     * When using wildcard origins, this must be false (tighten CORS + origins later for SPA cookies).
     */
    'supports_credentials' => $wildcardOrigins ? false : filter_var(env('CORS_SUPPORTS_CREDENTIALS', true), FILTER_VALIDATE_BOOL),
];

