<?php

/*
|--------------------------------------------------------------------------
| EmailJS
|--------------------------------------------------------------------------
|
| Credentials for the password-reset emails.
|
| These are read here rather than with env() inside the service so that
| `php artisan config:cache` does not blank them out — env() returns null
| once the config is cached.
|
| IMPORTANT: the send happens server-side, from Laravel, using the REST API
| and the PRIVATE key. It deliberately does NOT use the browser snippet.
| EmailJS's client library needs the message contents in JavaScript, which
| would put the verification code in the page where anyone could read it —
| including a code emailed to somebody else's address.
|
| Add to your .env:
|
|   EMAILJS_SERVICE_ID=service_xxxxxxx
|   EMAILJS_PUBLIC_KEY=your_public_key_here
|   EMAILJS_PRIVATE_KEY=your_private_key_here
|   EMAILJS_TEMPLATE_VERIFY=template_xxxxxxx
|   EMAILJS_TEMPLATE_RESET=template_xxxxxxx
|
| The private key is under Account → General → API Keys in the EmailJS
| dashboard. Turn OFF "Allow EmailJS API for non-browser applications"
| being required only if sending fails — with the private key supplied,
| server-side calls are permitted.
|
*/

return [

    'service_id' => env('EMAILJS_SERVICE_ID'),
    'public_key' => env('EMAILJS_PUBLIC_KEY'),
    'private_key' => env('EMAILJS_PRIVATE_KEY'),

    'templates' => [
        // Sends the 6-digit verification code.
        'verify' => env('EMAILJS_TEMPLATE_VERIFY'),
        // Confirms the password was changed.
        'reset' => env('EMAILJS_TEMPLATE_RESET'),
    ],

    'endpoint' => 'https://api.emailjs.com/api/v1.0/email/send',

    // How long a verification code stays valid.
    'code_ttl_minutes' => 15,

    /*
    | Branding used inside the email templates.
    |
    | logo_url MUST be publicly reachable. Mail clients fetch images from
    | their own servers, so a localhost / 127.0.0.1 / 192.168.x.x address
    | shows as a broken image to everyone but you. Until the site is on a
    | real domain, host the logo somewhere public and point this at it.
    |
    |   EMAILJS_LOGO_URL=https://example.com/images/PSU-Logo.png
    |   EMAILJS_SITE_URL=https://upskill.psu.edu.ph
    */
    'logo_url' => env('EMAILJS_LOGO_URL'),
    'site_url' => env('EMAILJS_SITE_URL'),

];
