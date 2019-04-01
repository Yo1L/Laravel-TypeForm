<?php
return [
    'debug' => true,
    'token' => env('TYPEFORM_TOKEN'),
    'headers' => [],
    'base_uri' => 'https://api.typeform.com/',
    'webhook' => [
        'base_uri' => env('TYPEFORM_WEBHOOK_BASE_URI', null), // if none app.url is used
        'uri' => env('TYPEFORM_WEBHOOK_URI', '/api/webhook/typeform'),
        'tag' => env('TYPEFORM_WEBHOOK_TAG', null),
        'secret' => env('TYPEFORM_WEBHOOK_SECRET', null),
        'verify_ssl' => env('TYPEFORM_WEBHOOK_VERIFY_SSL', true),
    ],
];
