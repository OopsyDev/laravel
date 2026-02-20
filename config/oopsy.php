<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Key
    |--------------------------------------------------------------------------
    |
    | The project DSN key used to authenticate error events.
    | Found in your project settings on the Oopsy dashboard.
    |
    */
    'key' => env('OOPSY_KEY'),

    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Oopsy instance to send events to.
    | Only change this if you are self-hosting Oopsy.
    |
    */
    'url' => env('OOPSY_URL', 'https://oopsy.dev'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The environment tag sent with each event (e.g. production, staging, local).
    |
    */
    'environment' => env('OOPSY_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Toggle error reporting on/off. Useful for disabling in local dev.
    |
    */
    'enabled' => env('OOPSY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Sample Rate
    |--------------------------------------------------------------------------
    |
    | A float between 0.0 and 1.0 representing the percentage of errors to send.
    | 1.0 means send all errors, 0.5 means send 50%.
    |
    */
    'sample_rate' => env('OOPSY_SAMPLE_RATE', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    |
    | Exception classes that should not be reported to Oopsy.
    |
    */
    'ignored_exceptions' => [
        Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        Illuminate\Validation\ValidationException::class,
        Illuminate\Auth\AuthenticationException::class,
        Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Send Default PII
    |--------------------------------------------------------------------------
    |
    | When false, no personally identifiable information (emails, IPs) is sent.
    |
    */
    'send_default_pii' => env('OOPSY_SEND_PII', false),

    /*
    |--------------------------------------------------------------------------
    | Breadcrumbs
    |--------------------------------------------------------------------------
    |
    | Configure which breadcrumb types to capture.
    |
    */
    'breadcrumbs' => [
        'logs' => true,
        'queries' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Lines
    |--------------------------------------------------------------------------
    |
    | Number of source code lines to capture around the error line.
    |
    */
    'context_lines' => 5,

    /*
    |--------------------------------------------------------------------------
    | Before Send Callback
    |--------------------------------------------------------------------------
    |
    | A callable that receives the event payload before sending.
    | Return the modified payload, or null to discard the event.
    |
    */
    'before_send' => null,
];
