<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
	 * SMS providers
	 */
    'vonage' => [
        'key'      => env('VONAGE_KEY', ''),
        'secret'   => env('VONAGE_SECRET', ''),
        'sms_from' => env('VONAGE_FROM', ''),
    ],

    'twilio' => [
        'account_sid'   => env('TWILIO_ACCOUNT_SID', ''),
        'account_mssid' => env('TWILIO_ACCOUNT_MSSID', ''),
        'auth_token'    => env('TWILIO_AUTH_TOKEN', ''),
        'from'          => env('TWILIO_SMS_FROM', ''),       // optional
        'call_from'     => env('TWILIO_CALL_FROM', ''),        // optional
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM', ''),   // optional
    ],

    'clicksend' => [
        'username' => env('CLICKSEND_USERNAME', ''),
        'api_key'  => env('CLICKSEND_API_KEY', ''),
        'from'     => env('CLICKSEND_FROM_NUMBER', ''),
    ],

];
