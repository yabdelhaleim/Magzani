<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Magzani's CORS policy is now driven by CORS_ALLOWED_ORIGINS (env) so that
| the same code can ship to local, staging, and production without a code
| change.
|
| Behavior:
|  - Production: env() returns `https://kayan.remotelly1.site,https://...`
|  - Local dev: falls back to ["*"] only when APP_ENV != production AND no
|    explicit list is provided. This keeps sandbox workflow easy without
|    weakening production.
|
| Browsers intentionally refuse `*` when `supports_credentials` is true —
| combining the two is the broader mistake we used to leak. We now require
| an explicit list when credentials are involved.
|
*/

return [

    // Endpoints that should receive CORS pre-flight and responses.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Origin allow-list. Read from env (comma-separated). Falls back to a
    // safer default in production. In dev we keep `*` for convenience.
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env(
            'CORS_ALLOWED_ORIGINS',
            env('APP_ENV') === 'production'
                ? 'https://kayan.remotelly1.site,https://superdashboard.remotelly1.site,https://bakedgekayan.remotelly1.site'
                : '*'
        ))
    ))),

    // Regex patterns matched against the Origin header (use sparingly).
    // Example: ^https://.*\.remotelly1\.site$ would allow any subdomain
    // of remotelly1.site over HTTPS.
    'allowed_origins_patterns' => array_values(array_filter(
        explode(',', (string) env('CORS_ALLOWED_ORIGINS_PATTERNS', ''))
    )),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    // Cache the pre-flight response for 1 day by default.
    'max_age' => (int) env('CORS_MAX_AGE', 86400),

    // Required to send cookies / Sanctum tokens across origins.
    // If true, the browser demands an explicit origin (not `*`).
    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', true),

];
