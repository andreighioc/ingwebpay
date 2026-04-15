<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ING WebPay Environment
    |--------------------------------------------------------------------------
    |
    | Determines which ING WebPay environment should be used.
    | Supported values: "test", "production"
    |
    */

    'environment' => env('INGWEBPAY_ENVIRONMENT', 'test'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | The API username and password assigned by ING Bank for the technical
    | contact of the merchant. Different credentials are provided for
    | each currency (RON / EUR) and for each environment (test / production).
    |
    */

    'username' => env('INGWEBPAY_USERNAME', ''),

    'password' => env('INGWEBPAY_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The ISO 4217 numeric currency code used for transactions:
    |   946 = RON (Romanian Leu)
    |   978 = EUR (Euro)
    |
    */

    'currency' => env('INGWEBPAY_CURRENCY', '946'),

    /*
    |--------------------------------------------------------------------------
    | Return URL
    |--------------------------------------------------------------------------
    |
    | The URL to which ING WebPay will redirect the payer after the
    | authorization of the transaction. This should point to a route
    | in your application that handles the payment callback.
    |
    */

    'return_url' => env('INGWEBPAY_RETURN_URL', '/ingwebpay/callback'),

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | The default language for the ING WebPay payment page.
    | Supported values: "ro" (Romanian), "en" (English)
    |
    */

    'language' => env('INGWEBPAY_LANGUAGE', 'ro'),

    /*
    |--------------------------------------------------------------------------
    | Order Number Mode
    |--------------------------------------------------------------------------
    |
    | Determines how the orderNumber is handled:
    |   "merchant" — The merchant sends the orderNumber (must be unique)
    |   "auto"     — ING WebPay generates the orderNumber automatically
    |
    | IMPORTANT: This setting must match the configuration set on the ING
    | WebPay side. Set it incorrectly and the transaction will be rejected.
    |
    */

    'order_number_mode' => env('INGWEBPAY_ORDER_NUMBER_MODE', 'merchant'),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    |
    | The base URLs for the ING WebPay REST API in both environments.
    | These should NOT be changed unless ING Bank communicates new endpoints.
    |
    */

    'base_urls' => [
        'test'       => 'https://securepay-uat.ing.ro/mpi_uat/rest',
        'production' => 'https://securepay.ing.ro/mpi/rest',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix used for the package's web routes (callback, success, failed).
    |
    */

    'route_prefix' => env('INGWEBPAY_ROUTE_PREFIX', 'ingwebpay'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the package routes.
    |
    */

    'route_middleware' => ['web'],

];
