<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailgun Domain
    |--------------------------------------------------------------------------
    |
    | The domain registered with Mailgun for sending emails and managing
    | mailing lists.
    |
    */

    'domain' => env('MAILGUN_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Mailgun API Keys
    |--------------------------------------------------------------------------
    |
    | The HTTP API key (secret) is used for mailing list management and
    | webhook verification. The sending API key is used for sending messages.
    | The standard API key is used for subscriber management.
    |
    */

    'secret' => env('MAILGUN_SECRET'),

    'api_key' => env('MAILGUN_API_KEY'),

    'sending_api_key' => env('MAILGUN_SENDING_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Mailgun API Endpoint
    |--------------------------------------------------------------------------
    |
    | The base URL for the Mailgun API. Use the EU endpoint if your domain
    | is registered in the EU region: https://api.eu.mailgun.net
    |
    */

    'endpoint' => env('MAILGUN_ENDPOINT', 'https://api.mailgun.net'),

    /*
    |--------------------------------------------------------------------------
    | Default Subscribers List
    |--------------------------------------------------------------------------
    |
    | The address of the default mailing list used for subscriber management.
    |
    */

    'subscribers_list' => env('MAILGUN_SUBSCRIBERS_LIST'),

];
