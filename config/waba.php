<?php

return [
    'token' => env('WABA_TOKEN'),
    'phone_number_id' => env('WABA_PHONE_NUMBER_ID'),
    'business_id' => env('WABA_BUSINESS_ID'),
    'account_id' => env('WABA_ACCOUNT_ID'),
    'verify_token' => env('WABA_VERIFY_TOKEN'),
    'version' => env('WABA_VERSION', 'v20.0'),
    'base_url' => env('WABA_BASE_URL', 'https://graph.facebook.com'),
];
